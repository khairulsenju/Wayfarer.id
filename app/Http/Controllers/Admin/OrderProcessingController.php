<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderProcessingController extends Controller
{
    public function __construct(private OrderService $orders)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $queueStatuses = [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED];

        $orders = Order::with(['user', 'items.product'])
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereIn('order_status', $queueStatuses)
            ->when(in_array($status, $queueStatuses, true), function ($query) use ($status) {
                $query->where('order_status', $status);
            })
            ->orderBy('paid_at')
            ->paginate(15)
            ->withQueryString();

        $transitions = [];

        foreach ($orders as $order) {
            $transitions[$order->id] = $this->orders->allowedTransitions($order);
        }

        return view('admin.processing.index', [
            'orders' => $orders,
            'transitions' => $transitions,
            'status' => $status,
            'statusLabels' => Order::STATUS_LABELS,
        ]);
    }

    public function update(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        try {
            $updated = $this->orders->changeStatus($order, $request->validated('status'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            'Status pesanan ' . $updated->order_code . ' diubah menjadi "' . $updated->orderStatusLabel() . '".'
        );
    }
}