<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RentalReturnController extends Controller
{
    public function __construct(private OrderService $orders)
    {
    }

    public function index(Request $request): View
    {
        $onlyOverdue = $request->boolean('overdue');
        $keyword = trim((string) $request->input('q', ''));

        $orders = Order::with(['user', 'items' => function ($query) {
            $query->where('type', 'rent')->with('product');
        }])
            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('order_status', Order::STATUS_RENTED)
            ->search($keyword)
            ->when($onlyOverdue, function ($query) {
                $query->whereHas('items', function ($items) {
                    $items->where('type', 'rent')
                        ->where('rent_end', '<', today()->toDateString());
                });
            })
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.returns.index', [
            'orders' => $orders,
            'onlyOverdue' => $onlyOverdue,
            'keyword' => $keyword,
            'today' => today(),
        ]);
    }

    public function store(Order $order): RedirectResponse
    {
        try {
            $updated = $this->orders->processReturn($order);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            'Pengembalian pesanan ' . $updated->order_code . ' berhasil dicatat. Unit sewa kembali tersedia.'
        );
    }
}