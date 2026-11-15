<?php

namespace App\Services;

use Carbon\Carbon;

class CartService
{
    public function __construct(private RentalAvailabilityService $availability)
    {
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines  Hasil PricingService::summarizeCartItems()['lines']
     * @return array<int, string>  Pesan masalah dengan key id cart item
     */
    public function issues(array $lines): array
    {
        $issues = [];

        foreach ($lines as $line) {
            $item = $line['cart_item'];
            $product = $line['product'];

            if (! $product->is_active) {
                $issues[$item->id] = 'Produk sudah tidak aktif.';

                continue;
            }

            if ($line['type'] === 'sale') {
                if (! $product->canBeSold()) {
                    $issues[$item->id] = 'Produk ini tidak lagi dijual.';

                    continue;
                }

                $stock = $this->availability->availableSaleStock($product);

                if ($stock < (int) $line['qty']) {
                    $issues[$item->id] = 'Stok beli tersisa ' . $stock . ' unit.';
                }

                continue;
            }

            if (! $product->canBeRented()) {
                $issues[$item->id] = 'Produk ini tidak lagi disewakan.';

                continue;
            }

            $start = Carbon::parse($line['rent_start'])->startOfDay();
            $end = Carbon::parse($line['rent_end'])->startOfDay();

            if ($start->lt(today())) {
                $issues[$item->id] = 'Tanggal mulai sewa sudah lewat. Ubah tanggal sewa.';

                continue;
            }

            $available = $this->availability->availableRentStock($product, $start, $end);

            if ($available < (int) $line['qty']) {
                $issues[$item->id] = 'Stok sewa pada tanggal tersebut tersisa ' . $available . ' unit.';
            }
        }

        return $issues;
    }
}