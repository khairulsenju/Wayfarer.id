<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2020,2100'],
        ]);

        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);

        $periodStart = Carbon::createFromDate($year, $month, 1)
            ->startOfMonth();

        $periodEnd = $periodStart->copy()->endOfMonth()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | PESANAN YANG SUDAH DIBAYAR
        |--------------------------------------------------------------------------
        */

        $paidOrders = Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereBetween('paid_at', [$periodStart, $periodEnd]);

        // Total uang yang benar-benar diterima.
        // Termasuk deposit sewa.
        $cashReceived = (clone $paidOrders)->sum('grand_total');

        // Nilai deposit yang terdapat dalam pesanan lunas.
        $depositTotal = (clone $paidOrders)->sum('deposit_total');

        $paidOrderCount = (clone $paidOrders)->count();

        /*
        |--------------------------------------------------------------------------
        | PENDAPATAN PENJUALAN
        |--------------------------------------------------------------------------
        */

        $saleIncome = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', Order::PAYMENT_PAID)
            ->whereBetween('orders.paid_at', [$periodStart, $periodEnd])
            ->where('order_items.type', OrderItem::TYPE_SALE)
            ->sum('order_items.subtotal');

        /*
        |--------------------------------------------------------------------------
        | PENDAPATAN PENYEWAAN
        |--------------------------------------------------------------------------
        */

        $rentalIncome = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', Order::PAYMENT_PAID)
            ->whereBetween('orders.paid_at', [$periodStart, $periodEnd])
            ->where('order_items.type', OrderItem::TYPE_RENT)
            ->sum('order_items.subtotal');

        // Pendapatan usaha tidak termasuk deposit.
        $totalIncome = $saleIncome + $rentalIncome;

        /*
        |--------------------------------------------------------------------------
        | PENGELUARAN
        |--------------------------------------------------------------------------
        */

        $expensesTotal = Expense::query()
            ->whereBetween('expense_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString()
            ])
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | SELISIH PENDAPATAN DAN PENGELUARAN
        |--------------------------------------------------------------------------
        */

        $netBalance = $totalIncome - $expensesTotal;

        /*
        |--------------------------------------------------------------------------
        | PEMBAYARAN YANG MASIH MENUNGGU
        |--------------------------------------------------------------------------
        */

        $pendingTotal = Order::query()
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('grand_total');

        /*
        |--------------------------------------------------------------------------
        | RIWAYAT PESANAN LUNAS
        |--------------------------------------------------------------------------
        */

        $orders = (clone $paidOrders)
            ->orderByDesc('paid_at')
            ->paginate(10, ['*'], 'order_page')
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | RIWAYAT PENGELUARAN
        |--------------------------------------------------------------------------
        */

        $expenses = Expense::query()
            ->whereBetween('expense_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString()
            ])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'expense_page')
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | GRAFIK ENAM BULAN
        |--------------------------------------------------------------------------
        */

        $chartStart = $periodStart->copy()
            ->subMonths(5)
            ->startOfMonth();

        $monthlyIncome = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', Order::PAYMENT_PAID)
            ->whereBetween('orders.paid_at', [
                $chartStart,
                $periodEnd
            ])
            ->whereIn('order_items.type', [
                OrderItem::TYPE_SALE,
                OrderItem::TYPE_RENT
            ])
            ->selectRaw("
                DATE_FORMAT(orders.paid_at, '%Y-%m') as period,
                SUM(order_items.subtotal) as total
            ")
            ->groupBy('period')
            ->pluck('total', 'period');

        $monthlyExpenses = Expense::query()
            ->whereBetween('expense_date', [
                $chartStart->toDateString(),
                $periodEnd->toDateString()
            ])
            ->selectRaw("
                DATE_FORMAT(expense_date, '%Y-%m') as period,
                SUM(amount) as total
            ")
            ->groupBy('period')
            ->pluck('total', 'period');

        $shortMonths = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $chartMonths = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = $periodStart->copy()->subMonths($i);
            $key = $date->format('Y-m');

            $chartMonths->push([
                'label' => $shortMonths[$date->month] . ' ' . $date->format('y'),
                'income' => (int) ($monthlyIncome[$key] ?? 0),
                'expense' => (int) ($monthlyExpenses[$key] ?? 0),
            ]);
        }

        $maxChartValue = max(
            1,
            (int) $chartMonths
                ->flatMap(fn ($item) => [
                    $item['income'],
                    $item['expense']
                ])
                ->max()
        );

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return view('admin.finance.index', compact(
            'month',
            'year',
            'monthNames',
            'cashReceived',
            'depositTotal',
            'paidOrderCount',
            'saleIncome',
            'rentalIncome',
            'totalIncome',
            'expensesTotal',
            'netBalance',
            'pendingTotal',
            'orders',
            'expenses',
            'chartMonths',
            'maxChartValue'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAH PENGELUARAN
    |--------------------------------------------------------------------------
    */

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category' => [
                'required',
                'in:pembelian_stok,operasional,perawatan,pengiriman,pengemasan,lainnya'
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],

            'amount' => [
                'required',
                'integer',
                'min:1',
                'max:999999999999'
            ],

            'expense_date' => [
                'required',
                'date'
            ],
        ]);

        Expense::create([
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.finance.index', [
            'month' => $request->input('filter_month', now()->month),
            'year' => $request->input('filter_year', now()->year),
        ])->with('success', 'Pengeluaran berhasil dicatat.');
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS PENGELUARAN
    |--------------------------------------------------------------------------
    */

    public function destroyExpense(Request $request, Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.finance.index', [
            'month' => $request->input('filter_month', now()->month),
            'year' => $request->input('filter_year', now()->year),
        ])->with('success', 'Data pengeluaran berhasil dihapus.');
    }
}
