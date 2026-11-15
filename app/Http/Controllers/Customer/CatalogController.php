<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\PricingService;
use App\Services\RentalAvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatalogController extends Controller
{
    public function __construct(
        private PricingService $pricing,
        private RentalAvailabilityService $availability
    ) {
    }

    public function index(Request $request): View
    {
        $keyword = trim((string) $request->input('q', ''));
        $mode = $request->input('mode');
        $categorySlug = $request->input('category');
        $sort = $request->input('sort', 'latest');

        $products = Product::active()
            ->with('category')
            ->search($keyword)
            ->ofMode($mode)
            ->when($categorySlug, function ($query) use ($categorySlug) {
                $query->whereHas('category', function ($category) use ($categorySlug) {
                    $category->where('slug', $categorySlug);
                });
            })
            ->when($sort === 'name', function ($query) {
                $query->orderBy('name');
            }, function ($query) {
                $query->latest();
            })
            ->paginate(12)
            ->withQueryString();

        return view('customer.catalog.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'modes' => Product::MODES,
            'keyword' => $keyword,
            'mode' => $mode,
            'categorySlug' => $categorySlug,
            'sort' => $sort,
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category');

        $rentStart = null;
        $rentEnd = null;
        $availableRent = null;
        $rentQuote = null;
        $dateErrors = [];

        $validator = Validator::make(
            $request->only(['rent_start', 'rent_end']),
            [
                'rent_start' => ['nullable', 'date', 'after_or_equal:today'],
                'rent_end' => ['nullable', 'date', 'after_or_equal:rent_start'],
            ],
            [
                'rent_start.date' => 'Tanggal mulai sewa tidak valid.',
                'rent_start.after_or_equal' => 'Tanggal mulai sewa tidak boleh sebelum hari ini.',
                'rent_end.date' => 'Tanggal selesai sewa tidak valid.',
                'rent_end.after_or_equal' => 'Tanggal selesai sewa tidak boleh sebelum tanggal mulai.',
            ]
        );

        if ($validator->fails()) {
            $dateErrors = $validator->errors()->all();
        } elseif ($product->canBeRented() && $request->filled('rent_start') && $request->filled('rent_end')) {
            [$rentStart, $rentEnd] = $this->pricing->normalizeRange(
                $request->input('rent_start'),
                $request->input('rent_end')
            );

            $availableRent = $this->availability->availableRentStock($product, $rentStart, $rentEnd);
            $rentQuote = $this->pricing->line($product, 'rent', 1, $rentStart, $rentEnd);
        }

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->orderBy('name')
            ->limit(4)
            ->get();

        return view('customer.catalog.show', [
            'product' => $product,
            'related' => $related,
            'availableSale' => $this->availability->availableSaleStock($product),
            'rentStart' => $rentStart,
            'rentEnd' => $rentEnd,
            'availableRent' => $availableRent,
            'rentQuote' => $rentQuote,
            'dateErrors' => $dateErrors,
        ]);
    }
}