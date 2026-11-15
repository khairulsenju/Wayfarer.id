<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'orders' => Order::count(),
            'pending_payment' => Order::where('payment_status', Order::PAYMENT_PENDING)->count(),
            'to_process' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->where('order_status', Order::STATUS_PROCESSING)
                ->count(),
            'to_deliver' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->where('order_status', Order::STATUS_SHIPPED)
                ->count(),
            'active_rentals' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->where('order_status', Order::STATUS_RENTED)
                ->count(),
            'overdue_rentals' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->where('order_status', Order::STATUS_RENTED)
                ->whereHas('items', function ($query) {
                    $query->where('type', 'rent')
                        ->where('rent_end', '<', today()->toDateString());
                })
                ->count(),
            'revenue' => (int) Order::where('payment_status', Order::PAYMENT_PAID)
                ->where('order_status', '!=', Order::STATUS_CANCELLED)
                ->sum('subtotal'),
            'deposit_held' => (int) Order::where('payment_status', Order::PAYMENT_PAID)
                ->whereIn('order_status', [
                    Order::STATUS_PROCESSING,
                    Order::STATUS_SHIPPED,
                    Order::STATUS_RENTED,
                    Order::STATUS_RETURNED,
                ])
                ->sum('deposit_total'),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->limit(8)
            ->get();

        $lowStockProducts = Product::active()
            ->with('category')
            ->where(function ($query) {
                $query->where(function ($sale) {
                    $sale->whereIn('mode', [Product::MODE_SALE, Product::MODE_BOTH])
                        ->where('stock_sale', '<=', 5);
                })->orWhere(function ($rent) {
                    $rent->whereIn('mode', [Product::MODE_RENT, Product::MODE_BOTH])
                        ->where('stock_rent', '<=', 2);
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}