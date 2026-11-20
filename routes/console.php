<?php

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:sync-pending-payments {--limit=100}', function (OrderService $orders) {
    $limit = max(1, min(500, (int) $this->option('limit')));

    $processed = 0;
    $changed = 0;

    Order::query()
        ->where('payment_status', Order::PAYMENT_PENDING)
        ->where('order_status', Order::STATUS_PENDING)
        ->orderBy('id')
        ->chunkById($limit, function ($pendingOrders) use ($orders, &$processed, &$changed) {
            foreach ($pendingOrders as $order) {
                $previousPaymentStatus = $order->payment_status;
                $previousOrderStatus = $order->order_status;

                $syncedOrder = $orders->syncPaymentStatus($order);
                $processed++;

                if (
                    $syncedOrder->payment_status !== $previousPaymentStatus
                    || $syncedOrder->order_status !== $previousOrderStatus
                ) {
                    $changed++;
                }
            }
        });

    $this->info('Pesanan pending diperiksa: ' . $processed);
    $this->info('Status pesanan yang berubah: ' . $changed);

    return self::SUCCESS;
})->purpose('Sinkronisasi status pembayaran pesanan pending dari Midtrans');

Schedule::command('orders:sync-pending-payments --limit=100')
    ->everyFiveMinutes()
    ->withoutOverlapping();
