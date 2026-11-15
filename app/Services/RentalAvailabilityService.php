<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use DateTimeInterface;

class RentalAvailabilityService
{
    public function __construct(private PricingService $pricing)
    {
    }

    public function reservedQuantity(
        int $productId,
        DateTimeInterface|string $start,
        DateTimeInterface|string $end,
        ?int $excludeOrderId = null
    ): int {
        [$startDate, $endDate] = $this->pricing->normalizeRange($start, $end);

        return (int) OrderItem::query()
            ->where('product_id', $productId)
            ->where('type', OrderItem::TYPE_RENT)
            ->where('rent_start', '<', $endDate->toDateString())
            ->where('rent_end', '>', $startDate->toDateString())
            ->whereHas('order', function ($query) use ($excludeOrderId) {
                $query->whereIn('payment_status', [Order::PAYMENT_PENDING, Order::PAYMENT_PAID])
                    ->whereNotIn('order_status', [
                        Order::STATUS_RETURNED,
                        Order::STATUS_COMPLETED,
                        Order::STATUS_CANCELLED,
                    ]);

                if ($excludeOrderId !== null) {
                    $query->where('id', '!=', $excludeOrderId);
                }
            })
            ->sum('qty');
    }

    public function availableRentStock(
        Product $product,
        DateTimeInterface|string $start,
        DateTimeInterface|string $end,
        ?int $excludeOrderId = null
    ): int {
        $reserved = $this->reservedQuantity($product->id, $start, $end, $excludeOrderId);

        return max(0, (int) $product->stock_rent - $reserved);
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

    public function availableSaleStock(Product $product): int
    {
        return max(0, (int) $product->stock_sale);
    }

    public function isSaleAvailable(Product $product, int $qty): bool
    {
        if (! $product->is_active || ! $product->canBeSold()) {
            return false;
        }

        return $this->availableSaleStock($product) >= $qty;
    }
}