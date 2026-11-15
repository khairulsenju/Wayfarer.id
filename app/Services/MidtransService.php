<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.is_sanitized');
        Config::$is3ds = (bool) config('midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): string
    {
        $this->assertConfigured();

        $order->loadMissing('items.product', 'user');

        $itemDetails = [];
        $sum = 0;

        foreach ($order->items as $item) {
            $isRent = $item->type === 'rent';

            $name = $isRent
                ? 'Sewa ' . $item->product->name . ' (' . $item->rent_days . ' hari)'
                : $item->product->name;

            $price = $isRent
                ? (int) $item->unit_price * (int) $item->rent_days
                : (int) $item->unit_price;

            $itemDetails[] = [
                'id' => 'ITEM-' . $item->id,
                'price' => $price,
                'quantity' => (int) $item->qty,
                'name' => Str::limit($name, 50, ''),
            ];

            $sum += $price * (int) $item->qty;

            if ((int) $item->deposit > 0) {
                $itemDetails[] = [
                    'id' => 'DEP-' . $item->id,
                    'price' => (int) $item->deposit,
                    'quantity' => 1,
                    'name' => Str::limit('Deposit ' . $item->product->name, 50, ''),
                ];

                $sum += (int) $item->deposit;
            }
        }

        if ($sum !== (int) $order->grand_total) {
            throw new RuntimeException('Total item tidak sama dengan grand total pesanan.');
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => (int) $order->grand_total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => Str::limit($order->recipient_name, 50, ''),
                'email' => $order->user->email,
                'phone' => $order->phone,
                'shipping_address' => [
                    'first_name' => Str::limit($order->recipient_name, 50, ''),
                    'phone' => $order->phone,
                    'address' => Str::limit($order->address, 200, ''),
                    'country_code' => 'IDN',
                ],
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => (int) config('midtrans.expiry_minutes', 60),
            ],
        ];

        return (string) Snap::getSnapToken($params);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function isSignatureValid(array $payload): bool
    {
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $key) {
            if (! isset($payload[$key]) || ! is_scalar($payload[$key])) {
                return false;
            }
        }

        $expected = hash(
            'sha512',
            $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . config('midtrans.server_key')
        );

        return hash_equals($expected, (string) $payload['signature_key']);
    }

    public function mapPaymentStatus(?string $transactionStatus, ?string $fraudStatus = null): string
    {
        return match ($transactionStatus) {
            'settlement' => Order::PAYMENT_PAID,
            'capture' => $fraudStatus === 'challenge' ? Order::PAYMENT_PENDING : Order::PAYMENT_PAID,
            'pending', 'authorize' => Order::PAYMENT_PENDING,
            'expire' => Order::PAYMENT_EXPIRED,
            'deny', 'cancel', 'failure' => Order::PAYMENT_FAILED,
            default => Order::PAYMENT_PENDING,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function checkStatus(string $orderCode): array
    {
        $this->assertConfigured();

        $status = Transaction::status($orderCode);

        $decoded = json_decode(json_encode($status), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function assertConfigured(): void
    {
        if (! config('midtrans.server_key')) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diatur di file .env.');
        }
    }
}