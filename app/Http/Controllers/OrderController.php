<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders)
    {
    }

    public function index(Request $request): View
    {
        $keyword = trim((string) $request->input('q', ''));
        $paymentStatus = $request->input('payment_status');
        $orderStatus = $request->input('order_status');

        $orders = Order::with('user')
            ->withCount('items')
            ->search($keyword)
            ->ofPaymentStatus($paymentStatus)
            ->ofOrderStatus($orderStatus)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'keyword' => $keyword,
            'paymentStatus' => $paymentStatus,
            'orderStatus' => $orderStatus,
            'paymentLabels' => Order::PAYMENT_LABELS,
            'statusLabels' => Order::STATUS_LABELS,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.product', 'payment', 'payments']);

        return view('admin.orders.show', [
            'order' => $order,
            'transitions' => $this->orders->allowedTransitions($order),
            'statusLabels' => Order::STATUS_LABELS,
        ]);
    }

    public function syncPayment(Order $order): RedirectResponse
    {
        if (! $order->isPending()) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('error', 'Status pembayaran hanya bisa disinkronkan untuk pesanan yang masih menunggu pembayaran.');
        }

        $updated = $this->orders->syncPaymentStatus($order);

        return redirect()
            ->route('admin.orders.show', $updated)
            ->with('success', 'Status pembayaran disinkronkan dari Midtrans: ' . $updated->paymentStatusLabel() . '.');
    }
}