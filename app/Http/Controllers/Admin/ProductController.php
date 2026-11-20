<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\RentalAvailabilityService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductController extends Controller
{
    public function __construct(
        private RentalAvailabilityService $availability
    ) {
    }

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
            $data['image'] = $request->file('image')
                ->store('products', 'public');
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

    public function update(
        ProductRequest $request,
        Product $product
    ): RedirectResponse {
        $newImage = null;
        $oldImage = null;

        try {
            $updatedProduct = DB::transaction(
                function () use (
                    $request,
                    $product,
                    &$newImage,
                    &$oldImage
                ) {
                    $lockedProduct = Product::query()
                        ->whereKey($product->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $data = $this->payload($request);

                    $mode = $data['mode'];

                    $sells = in_array($mode, [
                        Product::MODE_SALE,
                        Product::MODE_BOTH
                    ], true);

                    $rents = in_array($mode, [
                        Product::MODE_RENT,
                        Product::MODE_BOTH
                    ], true);

                    $reservedSale =
                        $this->availability->reservedSaleQuantity(
                            $lockedProduct->id,
                            null,
                            true
                        );

                    $requiredRentStock =
                        $this->availability->maxConcurrentRentQuantity(
                            $lockedProduct->id,
                            true
                        );

                    if (
                        $reservedSale > 0 &&
                        (! $sells ||
                        (int) $data['stock_sale'] < $reservedSale)
                    ) {
                        throw new DomainException(
                            'Stok jual tidak dapat dikurangi atau dinonaktifkan karena masih ada '
                            . $reservedSale
                            . ' unit yang direservasi oleh pesanan menunggu pembayaran.'
                        );
                    }

                    if (
                        $requiredRentStock > 0 &&
                        (! $rents ||
                        (int) $data['stock_rent'] < $requiredRentStock)
                    ) {
                        throw new DomainException(
                            'Stok sewa tidak dapat dikurangi atau dinonaktifkan. Minimal '
                            . $requiredRentStock
                            . ' unit masih dibutuhkan oleh reservasi atau penyewaan aktif.'
                        );
                    }

                    if ($lockedProduct->name !== $data['name']) {
                        $data['slug'] = $lockedProduct->makeUniqueSlug(
                            $data['name']
                        );
                    }

                    if ($request->hasFile('image')) {
                        $oldImage = $lockedProduct->image;

                        $newImage = $request->file('image')
                            ->store('products', 'public');

                        $data['image'] = $newImage;
                    } elseif ($request->boolean('remove_image')) {
                        $oldImage = $lockedProduct->image;
                        $data['image'] = null;
                    }

                    $lockedProduct->update($data);

                    return $lockedProduct->fresh();
                }
            );
        } catch (DomainException $e) {
            if ($newImage) {
                $this->deleteImage($newImage);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            if ($newImage) {
                $this->deleteImage($newImage);
            }

            throw $e;
        }

        if (
            $oldImage &&
            $oldImage !== $updatedProduct->image
        ) {
            $this->deleteImage($oldImage);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return redirect()
                ->route('admin.products.index')
                ->with(
                    'error',
                    'Produk "' . $product->name
                    . '" tidak bisa dihapus karena sudah ada di riwayat pesanan. '
                    . 'Nonaktifkan produk sebagai gantinya.'
                );
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

        $sells = in_array($mode, [
            Product::MODE_SALE,
            Product::MODE_BOTH
        ], true);

        $rents = in_array($mode, [
            Product::MODE_RENT,
            Product::MODE_BOTH
        ], true);

        return [
            'category_id' => (int) $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'mode' => $mode,

            'sale_price' => $sells
                ? (int) ($validated['sale_price'] ?? 0)
                : 0,

            'stock_sale' => $sells
                ? (int) ($validated['stock_sale'] ?? 0)
                : 0,

            'rent_price_per_day' => $rents
                ? (int) ($validated['rent_price_per_day'] ?? 0)
                : 0,

            'deposit' => $rents
                ? (int) ($validated['deposit'] ?? 0)
                : 0,

            'stock_rent' => $rents
                ? (int) ($validated['stock_rent'] ?? 0)
                : 0,

            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function deleteImage(?string $path): void
    {
        if (
            $path &&
            Storage::disk('public')->exists($path)
        ) {
            Storage::disk('public')->delete($path);
        }
    }
}
