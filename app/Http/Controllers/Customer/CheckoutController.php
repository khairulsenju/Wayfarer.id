<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PricingService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        private PricingService $pricing,
        private CartService $cartService,
        private OrderService $orders
    ) {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $items = Cart::forUser($user->id)->items()->with('product')->orderBy('id')->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang masih kosong.');
        }

        $summary = $this->pricing->summarizeCartItems($items);

        $issues = $this->cartService->issues($summary['lines']);

        if (count($issues) > 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Perbaiki item bermasalah di keranjang sebelum checkout.');
        }

        return view('customer.checkout.show', [
            'summary' => $summary,
            'user' => $user,
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        try {
            $order = $this->orders->createFromCart($request->user(), $request->validated());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('checkout.pay', $order);
    }

    public function pay(Request $request, Order $order): View|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if (! $order->isPending() || $order->order_status !== Order::STATUS_PENDING) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Pesanan ini tidak bisa dibayar.');
        }

        try {
            $snapToken = $this->orders->ensureSnapToken($order);
        } catch (Throwable $e) {
            Log::error('Gagal menyiapkan pembayaran: ' . $e->getMessage(), ['order_code' => $order->order_code]);

            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Gagal menyiapkan pembayaran. Silakan coba lagi.');
        }

        $snapJsUrl = config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';

        return view('customer.checkout.pay', [
            'order' => $order,
            'snapToken' => $snapToken,
            'clientKey' => config('midtrans.client_key'),
            'snapJsUrl' => $snapJsUrl,
            'finishUrl' => route('checkout.finish', $order),
            'orderUrl' => route('orders.show', $order),
        ]);
    }

    public function finish(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $updated = $this->orders->syncPaymentStatus($order);

        if ($updated->isPaid()) {
            return redirect()
                ->route('orders.show', $updated)
                ->with('success', 'Pembayaran berhasil. Pesanan Anda sedang diproses.');
        }

        if ($updated->isPending()) {
            return redirect()
                ->route('orders.show', $updated)
                ->with('info', 'Pembayaran belum selesai. Status akan diperbarui otomatis setelah pembayaran diterima.');
        }

        return redirect()
            ->route('orders.show', $updated)
            ->with('error', 'Pembayaran tidak berhasil: ' . $updated->paymentStatusLabel() . '.');
    }
}