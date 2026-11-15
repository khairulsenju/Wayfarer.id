<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class OrderService
{
    public function __construct(
        private PricingService $pricing,
        private RentalAvailabilityService $availability,
        private MidtransService $midtrans
    ) {
    }

    /**
     * @param  array{recipient_name: string, phone: string, address: string}  $shipping
     */
    public function createFromCart(User $user, array $shipping): Order
    {
        [$order, $cartItemIds] = DB::transaction(function () use ($user, $shipping) {
            $cart = Cart::where('user_id', $user->id)->lockForUpdate()->first();

            $items = $cart ? $cart->items()->get() : collect();

            if ($items->isEmpty()) {
                throw new DomainException('Keranjang masih kosong.');
            }

            $productIds = $items->pluck('product_id')->unique()->sort()->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $reservedSale = [];
            $reservedRent = [];

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product || ! $product->is_active) {
                    throw new DomainException('Salah satu produk di keranjang sudah tidak tersedia.');
                }

                if ($item->type === CartItem::TYPE_SALE) {
                    if (! $product->canBeSold()) {
                        throw new DomainException($product->name . ' tidak dijual.');
                    }

                    $already = $reservedSale[$product->id] ?? 0;

                    if ((int) $product->stock_sale < $already + (int) $item->qty) {
                        throw new DomainException('Stok beli ' . $product->name . ' tidak mencukupi.');
                    }

                    $reservedSale[$product->id] = $already + (int) $item->qty;

                    $lines[] = $this->pricing->line($product, 'sale', (int) $item->qty);

                    continue;
                }

                if (! $product->canBeRented()) {
                    throw new DomainException($product->name . ' tidak disewakan.');
                }

                if (! $item->rent_start || ! $item->rent_end) {
                    throw new DomainException('Tanggal sewa ' . $product->name . ' belum lengkap.');
                }

                [$start, $end] = $this->pricing->normalizeRange($item->rent_start, $item->rent_end);

                if ($start->lt(today())) {
                    throw new DomainException('Tanggal mulai sewa ' . $product->name . ' sudah lewat.');
                }

                $available = $this->availability->availableRentStock($product, $start, $end);

                $overlapInCart = 0;

                foreach ($reservedRent[$product->id] ?? [] as $reserved) {
                    if ($start->lt($reserved['end']) && $end->gt($reserved['start'])) {
                        $overlapInCart += $reserved['qty'];
                    }
                }

                if ($available - $overlapInCart < (int) $item->qty) {
                    throw new DomainException(
                        'Stok sewa ' . $product->name . ' pada ' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y') . ' tidak mencukupi.'
                    );
                }

                $reservedRent[$product->id][] = [
                    'start' => $start,
                    'end' => $end,
                    'qty' => (int) $item->qty,
                ];

                $lines[] = $this->pricing->line($product, 'rent', (int) $item->qty, $start, $end);
            }

            $totals = $this->pricing->totals($lines);

            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => $this->generateOrderCode(),
                'recipient_name' => $shipping['recipient_name'],
                'phone' => $shipping['phone'],
                'address' => $shipping['address'],
                'subtotal' => $totals['subtotal'],
                'deposit_total' => $totals['deposit_total'],
                'grand_total' => $totals['grand_total'],
                'payment_status' => Order::PAYMENT_PENDING,
                'order_status' => Order::STATUS_PENDING,
            ]);

            foreach ($lines as $line) {
                OrderItem::create(['order_id' => $order->id] + $line);

                if ($line['type'] === 'sale') {
                    Product::whereKey($line['product_id'])->decrement('stock_sale', $line['qty']);
                }
            }

            Payment::create([
                'order_id' => $order->id,
                'midtrans_order_id' => $order->order_code,
                'transaction_status' => 'pending',
                'payment_type' => null,
                'gross_amount' => $order->grand_total,
                'raw_response' => null,
                'signature_valid' => false,
            ]);

            return [$order, $items->pluck('id')->all()];
        });

        try {
            $token = $this->midtrans->createSnapToken($order);
        } catch (Throwable $e) {
            Log::error('Gagal membuat Snap token: ' . $e->getMessage(), ['order_code' => $order->order_code]);

            $this->closeOrder($order, Order::PAYMENT_FAILED);

            throw new DomainException('Gagal membuat transaksi pembayaran. Silakan coba lagi.', 0, $e);
        }

        $order->update(['snap_token' => $token]);

        CartItem::whereIn('id', $cartItemIds)->delete();

        return $order->refresh();
    }

    public function ensureSnapToken(Order $order): string
    {
        if (! $order->isPending() || $order->order_status !== Order::STATUS_PENDING) {
            throw new DomainException('Pesanan ini tidak bisa dibayar.');
        }

        if ($order->snap_token) {
            return $order->snap_token;
        }

        $token = $this->midtrans->createSnapToken($order);

        $order->update(['snap_token' => $token]);

        return $token;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleMidtransNotification(array $payload): Order
    {
        $orderCode = isset($payload['order_id']) && is_scalar($payload['order_id'])
            ? (string) $payload['order_id']
            : null;

        $order = $orderCode ? Order::where('order_code', $orderCode)->first() : null;

        if (! $order) {
            throw new ModelNotFoundException('Pesanan tidak ditemukan.');
        }

        $signatureValid = $this->midtrans->isSignatureValid($payload);

        $grossAmount = (int) round((float) ($payload['gross_amount'] ?? 0));
        $amountMatches = $grossAmount === (int) $order->grand_total;

        if (! $signatureValid || ! $amountMatches) {
            Payment::create([
                'order_id' => $order->id,
                'midtrans_order_id' => $order->order_code,
                'transaction_status' => (string) ($payload['transaction_status'] ?? 'unknown'),
                'payment_type' => isset($payload['payment_type']) ? (string) $payload['payment_type'] : null,
                'gross_amount' => $grossAmount,
                'raw_response' => $payload,
                'signature_valid' => false,
            ]);

            throw new InvalidArgumentException('Signature atau nominal notifikasi Midtrans tidak valid.');
        }

        return $this->applyPayload($order, $payload);
    }

    public function syncPaymentStatus(Order $order): Order
    {
        if (! $order->isPending()) {
            return $order;
        }

        try {
            $payload = $this->midtrans->checkStatus($order->order_code);
        } catch (Throwable $e) {
            Log::warning('Gagal mengambil status Midtrans: ' . $e->getMessage(), ['order_code' => $order->order_code]);

            return $order;
        }

        if (empty($payload['transaction_status'])) {
            return $order;
        }

        return $this->applyPayload($order, $payload);
    }

    /**
     * @return array<int, string>
     */
    public function allowedTransitions(Order $order): array
    {
        if (! $order->isPaid()) {
            return [];
        }

        return match ($order->order_status) {
            Order::STATUS_PROCESSING => [Order::STATUS_SHIPPED],
            Order::STATUS_SHIPPED => $order->hasRentItems()
                ? [Order::STATUS_RENTED]
                : [Order::STATUS_COMPLETED],
            Order::STATUS_RENTED => [Order::STATUS_RETURNED],
            Order::STATUS_RETURNED => [Order::STATUS_COMPLETED],
            default => [],
        };
    }

    public function changeStatus(Order $order, string $to): Order
    {
        return DB::transaction(function () use ($order, $to) {
            $locked = Order::with('items')->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($to, $this->allowedTransitions($locked), true)) {
                throw new DomainException('Perubahan status pesanan tidak diizinkan.');
            }

            $locked->update(['order_status' => $to]);

            return $locked;
        });
    }

    public function processReturn(Order $order): Order
    {
        if ($order->order_status !== Order::STATUS_RENTED) {
            throw new DomainException('Hanya pesanan berstatus "Sedang Disewa" yang bisa diproses pengembaliannya.');
        }

        return $this->changeStatus($order, Order::STATUS_RETURNED);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyPayload(Order $order, array $payload): Order
    {
        return DB::transaction(function () use ($order, $payload) {
            $locked = Order::with(['items', 'payment'])->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $newStatus = $this->midtrans->mapPaymentStatus(
                $payload['transaction_status'] ?? null,
                $payload['fraud_status'] ?? null
            );

            $alreadyPaid = $locked->payment_status === Order::PAYMENT_PAID;

            if (! $alreadyPaid || $newStatus === Order::PAYMENT_PAID) {
                $this->storePayment($locked, $payload);
            }

            if ($alreadyPaid) {
                return $locked->refresh();
            }

            if ($locked->payment_status !== Order::PAYMENT_PENDING) {
                Log::warning('Notifikasi Midtrans diterima untuk pesanan yang sudah ditutup.', [
                    'order_code' => $locked->order_code,
                    'payment_status' => $locked->payment_status,
                    'incoming' => $newStatus,
                ]);

                return $locked->refresh();
            }

            if ($newStatus === Order::PAYMENT_PAID) {
                $locked->update([
                    'payment_status' => Order::PAYMENT_PAID,
                    'order_status' => Order::STATUS_PROCESSING,
                    'paid_at' => now(),
                ]);
            } elseif ($newStatus === Order::PAYMENT_EXPIRED || $newStatus === Order::PAYMENT_FAILED) {
                $this->releaseSaleStock($locked);

                $locked->update([
                    'payment_status' => $newStatus,
                    'order_status' => Order::STATUS_CANCELLED,
                ]);
            }

            return $locked->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storePayment(Order $order, array $payload): void
    {
        $data = [
            'midtrans_order_id' => $order->order_code,
            'transaction_status' => (string) ($payload['transaction_status'] ?? 'pending'),
            'payment_type' => isset($payload['payment_type']) ? (string) $payload['payment_type'] : null,
            'gross_amount' => (int) round((float) ($payload['gross_amount'] ?? $order->grand_total)),
            'raw_response' => $payload,
            'signature_valid' => true,
        ];

        if ($order->payment) {
            $order->payment->update($data);

            return;
        }

        Payment::create(['order_id' => $order->id] + $data);
    }

    private function closeOrder(Order $order, string $paymentStatus): void
    {
        DB::transaction(function () use ($order, $paymentStatus) {
            $locked = Order::with('items')->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->payment_status !== Order::PAYMENT_PENDING) {
                return;
            }

            $this->releaseSaleStock($locked);

            $locked->update([
                'payment_status' => $paymentStatus,
                'order_status' => Order::STATUS_CANCELLED,
            ]);

            Payment::where('order_id', $locked->id)->update([
                'transaction_status' => $paymentStatus === Order::PAYMENT_EXPIRED ? 'expire' : 'failure',
            ]);
        });
    }

    private function releaseSaleStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->type === OrderItem::TYPE_SALE) {
                Product::whereKey($item->product_id)->increment('stock_sale', $item->qty);
            }
        }
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'WYF-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}