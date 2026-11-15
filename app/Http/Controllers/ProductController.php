<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->input('q', ''));
        $mode = $request->input('mode');
        $categoryId = $request->input('category_id');
        $status = $request->input('status');

        $products = Product::with('category')
            ->search($keyword)
            ->ofMode($mode)
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($status === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'keyword' => $keyword,
            'mode' => $mode,
            'categoryId' => $categoryId,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::orderBy('name')->get(),
            'modes' => Product::MODES,
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $this->payload($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'modes' => Product::MODES,
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->payload($request);

        if ($product->name !== $data['name']) {
            $data['slug'] = $product->makeUniqueSlug($data['name']);
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($product->image);
            $data['image'] = null;
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', 'Produk "' . $product->name . '" tidak bisa dihapus karena sudah ada di riwayat pesanan. Nonaktifkan produk sebagai gantinya.');
        }

        $this->deleteImage($product->image);

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ProductRequest $request): array
    {
        $validated = $request->validated();

        $mode = $validated['mode'];
        $sells = in_array($mode, [Product::MODE_SALE, Product::MODE_BOTH], true);
        $rents = in_array($mode, [Product::MODE_RENT, Product::MODE_BOTH], true);

        return [
            'category_id' => (int) $validated['category_id'],
            'name' => $validated['name'],
            'mode' => $mode,
            'sale_price' => $sells ? (int) ($validated['sale_price'] ?? 0) : 0,
            'stock_sale' => $sells ? (int) ($validated['stock_sale'] ?? 0) : 0,
            'rent_price_per_day' => $rents ? (int) ($validated['rent_price_per_day'] ?? 0) : 0,
            'deposit' => $rents ? (int) ($validated['deposit'] ?? 0) : 0,
            'stock_rent' => $rents ? (int) ($validated['stock_rent'] ?? 0) : 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}