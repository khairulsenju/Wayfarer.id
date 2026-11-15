<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = DB::table('users')->where('role', 'customer')->orderBy('id')->get();
        $budi = $customers[0];
        $siti = $customers[1];

        $today = Carbon::today();

        $this->createOrder(
            [
                'user' => $budi,
                'order_code' => 'WYF-' . $today->format('Ymd') . '-0001',
                'recipient_name' => 'Budi Santoso',
                'phone' => '081200000002',
                'address' => 'Jl. Sudirman No. 10, Pekanbaru, Riau',
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'transaction_status' => 'settlement',
                'payment_type' => 'bank_transfer',
                'paid_at' => now()->subDays(2),
            ],
            [
                ['Daypack 25L', 'sale', 1, null, null],
                ['Dry Bag 20L', 'sale', 2, null, null],
            ]
        );

        $this->createOrder(
            [
                'user' => $siti,
                'order_code' => 'WYF-' . $today->format('Ymd') . '-0002',
                'recipient_name' => 'Siti Rahmawati',
                'phone' => '081200000003',
                'address' => 'Jl. Soekarno Hatta No. 25, Pekanbaru, Riau',
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'transaction_status' => 'pending',
                'payment_type' => null,
                'paid_at' => null,
            ],
            [
                ['Tenda Dome 2 Orang', 'rent', 1, $today->copy()->addDays(5), $today->copy()->addDays(8)],
            ]
        );

        $this->createOrder(
            [
                'user' => $budi,
                'order_code' => 'WYF-' . $today->format('Ymd') . '-0003',
                'recipient_name' => 'Budi Santoso',
                'phone' => '081200000002',
                'address' => 'Jl. Sudirman No. 10, Pekanbaru, Riau',
                'payment_status' => 'paid',
                'order_status' => 'rented',
                'transaction_status' => 'settlement',
                'payment_type' => 'gopay',
                'paid_at' => now()->subDays(1),
            ],
            [
                ['Carrier 60L', 'rent', 1, $today->copy()->subDay(), $today->copy()->addDays(2)],
                ['Gas Kaleng Butane Isi 3', 'sale', 2, null, null],
            ]
        );
    }

    private function createOrder(array $header, array $lines): void
    {
        $now = now();
        $subtotal = 0;
        $depositTotal = 0;
        $itemRows = [];

        foreach ($lines as [$productName, $type, $qty, $rentStart, $rentEnd]) {
            $product = DB::table('products')->where('name', $productName)->first();

            if ($type === 'rent') {
                $rentDays = max(1, $rentStart->diffInDays($rentEnd));
                $unitPrice = $product->rent_price_per_day;
                $lineDeposit = $product->deposit * $qty;
                $lineSubtotal = $unitPrice * $rentDays * $qty;
            } else {
                $rentDays = null;
                $unitPrice = $product->sale_price;
                $lineDeposit = 0;
                $lineSubtotal = $unitPrice * $qty;

                DB::table('products')->where('id', $product->id)->decrement('stock_sale', $qty);
            }

            $subtotal += $lineSubtotal;
            $depositTotal += $lineDeposit;

            $itemRows[] = [
                'product_id' => $product->id,
                'type' => $type,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'rent_start' => $rentStart ? $rentStart->toDateString() : null,
                'rent_end' => $rentEnd ? $rentEnd->toDateString() : null,
                'rent_days' => $rentDays,
                'deposit' => $lineDeposit,
                'subtotal' => $lineSubtotal,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $grandTotal = $subtotal + $depositTotal;

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $header['user']->id,
            'order_code' => $header['order_code'],
            'recipient_name' => $header['recipient_name'],
            'phone' => $header['phone'],
            'address' => $header['address'],
            'subtotal' => $subtotal,
            'deposit_total' => $depositTotal,
            'grand_total' => $grandTotal,
            'payment_status' => $header['payment_status'],
            'order_status' => $header['order_status'],
            'snap_token' => null,
            'paid_at' => $header['paid_at'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($itemRows as $row) {
            DB::table('order_items')->insert(['order_id' => $orderId] + $row);
        }

        DB::table('payments')->insert([
            'order_id' => $orderId,
            'midtrans_order_id' => $header['order_code'],
            'transaction_status' => $header['transaction_status'],
            'payment_type' => $header['payment_type'],
            'gross_amount' => $grandTotal,
            'raw_response' => json_encode([
                'order_id' => $header['order_code'],
                'transaction_status' => $header['transaction_status'],
                'payment_type' => $header['payment_type'],
                'gross_amount' => (string) $grandTotal . '.00',
                'source' => 'seeder',
            ]),
            'signature_valid' => $header['payment_status'] === 'paid',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}