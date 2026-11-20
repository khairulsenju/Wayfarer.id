<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class RentalAvailabilityService
{
    public function __construct(private PricingService $pricing)
    {
    }

    /**
     * Jumlah stok jual yang sedang direservasi oleh pesanan pending.
     * Stok fisik baru dikurangi setelah pembayaran terverifikasi.
     */
    public function reservedSaleQuantity(
        int $productId,
        ?int $excludeOrderId = null,
        bool $lockForUpdate = false
    ): int {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('oi.product_id', $productId)
            ->where('oi.type', OrderItem::TYPE_SALE)
            ->where('o.payment_status', Order::PAYMENT_PENDING)
            ->where('o.order_status', Order::STATUS_PENDING)
            ->select('oi.qty');

        if ($excludeOrderId !== null) {
            $query->where('o.id', '!=', $excludeOrderId);
        }

        if ($lockForUpdate) {
            return (int) $query->lockForUpdate()->get()->sum('qty');
        }

        return (int) $query->sum('oi.qty');
    }

    public function reservedQuantity(
        int $productId,
        DateTimeInterface|string $start,
        DateTimeInterface|string $end,
        ?int $excludeOrderId = null,
        bool $lockForUpdate = false
    ): int {
        [$startDate, $endDate] = $this->pricing->normalizeRange($start, $end);

        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('oi.product_id', $productId)
            ->where('oi.type', OrderItem::TYPE_RENT)
            ->where(function (Builder $reservations) use ($startDate, $endDate) {
                $reservations->where(function (Builder $overlap) use ($startDate, $endDate) {
                    $overlap->where('oi.rent_start', '<', $endDate->toDateString())
                        ->where('oi.rent_end', '>', $startDate->toDateString())
                        ->where(function (Builder $statuses) {
                            $statuses->where(function (Builder $pending) {
                                $pending->where('o.payment_status', Order::PAYMENT_PENDING)
                                    ->where('o.order_status', Order::STATUS_PENDING);
                            })->orWhere(function (Builder $paid) {
                                $paid->where('o.payment_status', Order::PAYMENT_PAID)
                                    ->whereIn('o.order_status', [
                                        Order::STATUS_PROCESSING,
                                        Order::STATUS_SHIPPED,
                                        Order::STATUS_RENTED,
                                    ]);
                            });
                        });
                })->orWhere(function (Builder $overdue) use ($endDate) {
                    // Rental berbayar yang melewati tanggal kembali tetap memakai unit
                    // sampai admin mengonfirmasi pengembaliannya.
                    $overdue->where('oi.rent_end', '<', today()->toDateString())
                        ->where('oi.rent_start', '<', $endDate->toDateString())
                        ->where('o.payment_status', Order::PAYMENT_PAID)
                        ->whereIn('o.order_status', [
                            Order::STATUS_PROCESSING,
                            Order::STATUS_SHIPPED,
                            Order::STATUS_RENTED,
                        ]);
                });
            });

        if ($excludeOrderId !== null) {
            $query->where('o.id', '!=', $excludeOrderId);
        }

        if ($lockForUpdate) {
            return (int) $query->lockForUpdate()->get()->sum('qty');
        }

        return (int) $query->sum('oi.qty');
    }

    public function availableRentStock(
        Product $product,
        DateTimeInterface|string $start,
        DateTimeInterface|string $end,
        ?int $excludeOrderId = null,
        bool $lockReservations = false
    ): int {
        $reserved = $this->reservedQuantity(
            $product->id,
            $start,
            $end,
            $excludeOrderId,
            $lockReservations
        );

        return max(0, (int) $product->stock_rent - $reserved);
    }

    /**
     * Jumlah rental terbanyak yang berjalan pada satu waktu.
     * Dipakai agar admin tidak mengurangi stok fisik di bawah booking aktif.
     */
    public function maxConcurrentRentQuantity(int $productId, bool $lockForUpdate = false): int
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('oi.product_id', $productId)
            ->where('oi.type', OrderItem::TYPE_RENT)
            ->whereNotNull('oi.rent_start')
            ->whereNotNull('oi.rent_end')
            ->where(function (Builder $statuses) {
                $statuses->where(function (Builder $pending) {
                    $pending->where('o.payment_status', Order::PAYMENT_PENDING)
                        ->where('o.order_status', Order::STATUS_PENDING);
                })->orWhere(function (Builder $paid) {
                    $paid->where('o.payment_status', Order::PAYMENT_PAID)
                        ->whereIn('o.order_status', [
                            Order::STATUS_PROCESSING,
                            Order::STATUS_SHIPPED,
                            Order::STATUS_RENTED,
                        ]);
                });
            })
            ->select([
                'oi.qty',
                'oi.rent_start',
                'oi.rent_end',
                'o.payment_status',
                'o.order_status',
            ]);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $items = $query->get();
        $events = [];
        $today = today()->toDateString();

        foreach ($items as $item) {
            $start = Carbon::parse($item->rent_start)->toDateString();
            $end = Carbon::parse($item->rent_end)->toDateString();
            $qty = (int) $item->qty;

            $events[$start] = ($events[$start] ?? 0) + $qty;

            $isOverdueAndNotReturned = $item->payment_status === Order::PAYMENT_PAID
                && in_array($item->order_status, [
                    Order::STATUS_PROCESSING,
                    Order::STATUS_SHIPPED,
                    Order::STATUS_RENTED,
                ], true)
                && $end < $today;

            if (! $isOverdueAndNotReturned) {
                $events[$end] = ($events[$end] ?? 0) - $qty;
            }
        }

        ksort($events);

        $current = 0;
        $maximum = 0;

        foreach ($events as $change) {
            $current += $change;
            $maximum = max($maximum, $current);
        }

        return max(0, $maximum);
    }

    public function isRentAvailable(
        Product $product,
        DateTimeInterface|string $start,
        DateTimeInterface|string $end,
        int $qty,
        ?int $excludeOrderId = null
    ): bool {
        if (! $product->is_active || ! $product->canBeRented()) {
            return false;
        }

        return $this->availableRentStock($product, $start, $end, $excludeOrderId) >= $qty;
    }

    public function availableSaleStock(Product $product, bool $lockReservations = false): int
    {
        $reserved = $this->reservedSaleQuantity($product->id, null, $lockReservations);

        return max(0, (int) $product->stock_sale - $reserved);
    }

    public function isSaleAvailable(Product $product, int $qty): bool
    {
        if (! $product->is_active || ! $product->canBeSold()) {
            return false;
        }

        return $this->availableSaleStock($product) >= $qty;
    }
}
