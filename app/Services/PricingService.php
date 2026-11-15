<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;

class PricingService
{
    public function toDate(DateTimeInterface|string $value): Carbon
    {
        $date = $value instanceof DateTimeInterface
            ? Carbon::instance($value)
            : Carbon::parse($value);

        return $date->startOfDay();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function normalizeRange(DateTimeInterface|string $start, DateTimeInterface|string $end): array
    {
        $startDate = $this->toDate($start);
        $endDate = $this->toDate($end);

        if ($endDate->lte($startDate)) {
            $endDate = $startDate->copy()->addDay();
        }

        return [$startDate, $endDate];
    }

    public function rentDays(DateTimeInterface|string $start, DateTimeInterface|string $end): int
    {
        $startDate = $this->toDate($start);
        $endDate = $this->toDate($end);

        return max(1, (int) round($startDate->diffInDays($endDate, false)));
    }

    /**
     * @return array<string, mixed>
     */
    public function line(
        Product $product,
        string $type,
        int $qty,
        DateTimeInterface|string|null $rentStart = null,
        DateTimeInterface|string|null $rentEnd = null
    ): array {
        if ($qty < 1) {
            throw new InvalidArgumentException('Jumlah minimal adalah 1.');
        }

        if ($type === 'rent') {
            if ($rentStart === null || $rentEnd === null) {
                throw new InvalidArgumentException('Tanggal sewa wajib diisi.');
            }

            [$start, $end] = $this->normalizeRange($rentStart, $rentEnd);

            $days = $this->rentDays($start, $end);
            $unitPrice = (int) $product->rent_price_per_day;
            $deposit = (int) $product->deposit * $qty;
            $subtotal = $unitPrice * $days * $qty;

            return [
                'product_id' => $product->id,
                'type' => 'rent',
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'rent_start' => $start->toDateString(),
                'rent_end' => $end->toDateString(),
                'rent_days' => $days,
                'deposit' => $deposit,
                'subtotal' => $subtotal,
            ];
        }

        $unitPrice = (int) $product->sale_price;

        return [
            'product_id' => $product->id,
            'type' => 'sale',
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'rent_start' => null,
            'rent_end' => null,
            'rent_days' => null,
            'deposit' => 0,
            'subtotal' => $unitPrice * $qty,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{subtotal: int, deposit_total: int, grand_total: int}
     */
    public function totals(array $lines): array
    {
        $subtotal = 0;
        $depositTotal = 0;

        foreach ($lines as $line) {
            $subtotal += (int) $line['subtotal'];
            $depositTotal += (int) $line['deposit'];
        }

        return [
            'subtotal' => $subtotal,
            'deposit_total' => $depositTotal,
            'grand_total' => $subtotal + $depositTotal,
        ];
    }

    /**
     * @param  iterable<\App\Models\CartItem>  $cartItems
     * @return array{lines: array<int, array<string, mixed>>, subtotal: int, deposit_total: int, grand_total: int}
     */
    public function summarizeCartItems(iterable $cartItems): array
    {
        $lines = [];

        foreach ($cartItems as $item) {
            $line = $this->line(
                $item->product,
                $item->type,
                (int) $item->qty,
                $item->rent_start,
                $item->rent_end
            );

            $line['cart_item'] = $item;
            $line['product'] = $item->product;

            $lines[] = $line;
        }

        return ['lines' => $lines] + $this->totals($lines);
    }

    public static function rupiah(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}