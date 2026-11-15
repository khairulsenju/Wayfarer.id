<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddToCartRequest;
use App\Http\Requests\Customer\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PricingService;
use App\Services\RentalAvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private PricingService $pricing,
        private RentalAvailabilityService $availability,
        private CartService $cartService
    ) {
    }

    public function index(Request $request): View
    {
        $cart = Cart::forUser($request->user()->id);

        $items = $cart->items()->with('product')->orderBy('id')->get();

        $summary = $this->pricing->summarizeCartItems($items);

        $issues = $this->cartService->issues($summary['lines']);

        return view('customer.cart.index', [
            'items' => $items,
            'summary' => $summary,
            'issues' => $issues,
            'hasIssues' => count($issues) > 0,
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $product = Product::active()->findOrFail($data['product_id']);
        $qty = (int) $data['qty'];
        $cart = Cart::forUser($request->user()->id);

        if ($data['type'] === CartItem::TYPE_SALE) {
            if (! $product->canBeSold()) {
                return back()->with('error', $product->name . ' tidak dijual.');
            }

            $existing = $cart->items()
                ->where('product_id', $product->id)
                ->where('type', CartItem::TYPE_SALE)
                ->first();

            $total = $qty + ($existing ? (int) $existing->qty : 0);

            if (! $this->availability->isSaleAvailable($product, $total)) {
                return back()->with(
                    'error',
                    'Stok beli ' . $product->name . ' tersisa ' . $this->availability->availableSaleStock($product) . ' unit.'
                );
            }

            if ($existing) {
                $existing->update(['qty' => $total]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'type' => CartItem::TYPE_SALE,
                    'qty' => $qty,
                ]);
            }

            return redirect()
                ->route('cart.index')
                ->with('success', $product->name . ' ditambahkan ke keranjang untuk dibeli.');
        }

        if (! $product->canBeRented()) {
            return back()->with('error', $product->name . ' tidak disewakan.');
        }

        [$start, $end] = $this->pricing->normalizeRange($data['rent_start'], $data['rent_end']);

        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->where('type', CartItem::TYPE_RENT)
            ->whereDate('rent_start', $start->toDateString())
            ->whereDate('rent_end', $end->toDateString())
            ->first();

        $total = $qty + ($existing ? (int) $existing->qty : 0);

        if (! $this->availability->isRentAvailable($product, $start, $end, $total)) {
            return back()->with(
                'error',
                'Stok sewa ' . $product->name . ' pada ' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y')
                . ' tersisa ' . $this->availability->availableRentStock($product, $start, $end) . ' unit.'
            );
        }

        if ($existing) {
            $existing->update(['qty' => $total]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'type' => CartItem::TYPE_RENT,
                'qty' => $qty,
                'rent_start' => $start->toDateString(),
                'rent_end' => $end->toDateString(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', $product->name . ' ditambahkan ke keranjang untuk disewa.');
    }

    public function update(UpdateCartItemRequest $request, int $item): RedirectResponse
    {
        $cart = Cart::forUser($request->user()->id);

        $cartItem = $cart->items()->with('product')->whereKey($item)->firstOrFail();

        $product = $cartItem->product;
        $qty = (int) $request->validated('qty');

        if (! $product->is_active) {
            return back()->with('error', 'Produk sudah tidak aktif. Hapus dari keranjang.');
        }

        if ($cartItem->type === CartItem::TYPE_SALE) {
            if (! $this->availability->isSaleAvailable($product, $qty)) {
                return back()->with(
                    'error',
                    'Stok beli ' . $product->name . ' tersisa ' . $this->availability->availableSaleStock($product) . ' unit.'
                );
            }

            $cartItem->update(['qty' => $qty]);

            return back()->with('success', 'Keranjang diperbarui.');
        }

        $startInput = $request->filled('rent_start') ? $request->input('rent_start') : $cartItem->rent_start;
        $endInput = $request->filled('rent_end') ? $request->input('rent_end') : $cartItem->rent_end;

        [$start, $end] = $this->pricing->normalizeRange($startInput, $endInput);

        if (! $this->availability->isRentAvailable($product, $start, $end, $qty)) {
            return back()->with(
                'error',
                'Stok sewa ' . $product->name . ' pada ' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y')
                . ' tersisa ' . $this->availability->availableRentStock($product, $start, $end) . ' unit.'
            );
        }

        $cartItem->update([
            'qty' => $qty,
            'rent_start' => $start->toDateString(),
            'rent_end' => $end->toDateString(),
        ]);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        $cart = Cart::forUser($request->user()->id);

        $cart->items()->whereKey($item)->firstOrFail()->delete();

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $cart = Cart::forUser($request->user()->id);

        $cart->items()->delete();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Keranjang dikosongkan.');
    }
}