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
            $reservedSaleInCart = [];
            $reservedRentInCart = [];

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product || ! $product->is_active) {
                    throw new DomainException('Salah satu produk di keranjang sudah tidak tersedia.');
                }

                if ($item->type === CartItem::TYPE_SALE) {
                    if (! $product->canBeSold()) {
                        throw new DomainException($product->name . ' tidak dijual.');
                    }

                    $alreadyInCart = $reservedSaleInCart[$product->id] ?? 0;
                    $available = $this->availability->availableSaleStock($product, true);

                    if ($available - $alreadyInCart < (int) $item->qty) {
                        throw new DomainException('Stok beli ' . $product->name . ' tidak mencukupi.');
                    }

                    $reservedSaleInCart[$product->id] = $alreadyInCart + (int) $item->qty;
                    $lines[] = $this->pricing->line($product, OrderItem::TYPE_SALE, (int) $item->qty);

                    continue;
                }

                if ($item->type !== CartItem::TYPE_RENT || ! $product->canBeRented()) {
                    throw new DomainException($product->name . ' tidak disewakan.');
                }

                if (! $item->rent_start || ! $item->rent_end) {
                    throw new DomainException('Tanggal sewa ' . $product->name . ' belum lengkap.');
                }

                [$start, $end] = $this->pricing->normalizeRange($item->rent_start, $item->rent_end);

                if ($start->lt(today())) {
                    throw new DomainException('Tanggal mulai sewa ' . $product->name . ' sudah lewat.');
                }

                $available = $this->availability->availableRentStock($product, $start, $end, null, true);
                $overlapInCart = 0;

                foreach ($reservedRentInCart[$product->id] ?? [] as $reserved) {
                    if ($start->lt($reserved['end']) && $end->gt($reserved['start'])) {
                        $overlapInCart += $reserved['qty'];
                    }
                }

                if ($available - $overlapInCart < (int) $item->qty) {
                    throw new DomainException(
                        'Stok sewa ' . $product->name . ' pada ' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y') . ' tidak mencukupi.'
                    );
                }

                $reservedRentInCart[$product->id][] = [
                    'start' => $start,
                    'end' => $end,
                    'qty' => (int) $item->qty,
                ];

                $lines[] = $this->pricing->line($product, OrderItem::TYPE_RENT, (int) $item->qty, $start, $end);
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

        $responseOrderCode = (string) ($payload['order_id'] ?? '');
        $responseAmount = (int) round((float) ($payload['gross_amount'] ?? 0));

        if ($responseOrderCode !== $order->order_code || $responseAmount !== (int) $order->grand_total) {
            Log::warning('Respons status Midtrans tidak cocok dengan pesanan.', [
                'order_code' => $order->order_code,
                'response_order_id' => $responseOrderCode,
                'response_amount' => $responseAmount,
            ]);

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
        return $this->changeStatus($order, Order::STATUS_RETURNED);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyPayload(Order $order, array $payload): Order
    {
        $newStatus = $this->midtrans->mapPaymentStatus(
            $payload['transaction_status'] ?? null,
            $payload['fraud_status'] ?? null
        );

        // Semua alur yang mengunci produk menggunakan urutan product -> order
        // untuk mengurangi risiko deadlock saat checkout dan webhook berjalan bersamaan.
        $saleProductIds = $newStatus === Order::PAYMENT_PAID
            ? OrderItem::query()
                ->where('order_id', $order->id)
                ->where('type', OrderItem::TYPE_SALE)
                ->distinct()
                ->orderBy('product_id')
                ->pluck('product_id')
            : collect();

        return DB::transaction(function () use ($order, $payload, $newStatus, $saleProductIds) {
            $lockedProducts = collect();

            if ($saleProductIds->isNotEmpty()) {
                $lockedProducts = Product::query()
                    ->whereIn('id', $saleProductIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
            }

            $locked = Order::with(['items', 'payment'])
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

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
                $this->consumeSaleStock($locked, $lockedProducts);

                $locked->update([
                    'payment_status' => Order::PAYMENT_PAID,
                    'order_status' => Order::STATUS_PROCESSING,
                    'paid_at' => now(),
                ]);
            } elseif (in_array($newStatus, [Order::PAYMENT_EXPIRED, Order::PAYMENT_FAILED], true)) {
                $locked->update([
                    'payment_status' => $newStatus,
                    'order_status' => Order::STATUS_CANCELLED,
                ]);
            }

            return $locked->refresh();
        });
    }

    /**
     * Kurangi stok fisik hanya sekali, ketika pembayaran berubah dari pending ke paid.
     */
    private function consumeSaleStock(Order $order, $products): void
    {
        $saleItems = $order->items
            ->where('type', OrderItem::TYPE_SALE)
            ->groupBy('product_id');

        if ($saleItems->isEmpty()) {
            return;
        }

        foreach ($saleItems as $productId => $items) {
            $product = $products->get($productId);
            $quantity = (int) $items->sum('qty');

            if (! $product || (int) $product->stock_sale < $quantity) {
                Log::critical('Stok fisik tidak mencukupi saat pembayaran terverifikasi.', [
                    'order_code' => $order->order_code,
                    'product_id' => $productId,
                    'required_quantity' => $quantity,
                    'available_physical_stock' => $product?->stock_sale,
                ]);

                throw new DomainException(
                    'Stok fisik produk tidak mencukupi saat pembayaran dikonfirmasi. Pesanan perlu diperiksa admin.'
                );
            }
        }

        foreach ($saleItems as $productId => $items) {
            $products->get($productId)->decrement('stock_sale', (int) $items->sum('qty'));
        }
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

            $locked->update([
                'payment_status' => $paymentStatus,
                'order_status' => Order::STATUS_CANCELLED,
            ]);

            Payment::where('order_id', $locked->id)->update([
                'transaction_status' => $paymentStatus === Order::PAYMENT_EXPIRED ? 'expire' : 'failure',
            ]);
        });
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'WYF-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}
