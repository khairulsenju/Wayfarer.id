<?php

namespace App\Http\Controllers\Customer;

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
        $orders = Order::where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if ($order->isPending()) {
            $order = $this->orders->syncPaymentStatus($order);
        }

        $order->load(['items.product', 'payment']);

        return view('customer.orders.show', [
            'order' => $order,
            'canPay' => $order->isPending() && $order->order_status === Order::STATUS_PENDING,
        ]);
    }

    public function invoice(Request $request, Order $order): View|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if (! $order->isPaid()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Invoice hanya tersedia untuk pesanan yang sudah dibayar.');
        }

        $order->load(['user', 'items.product', 'payment']);

        return view('customer.orders.invoice', [
            'order' => $order,
        ]);
    }
}