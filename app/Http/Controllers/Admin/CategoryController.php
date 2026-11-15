<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->input('q', ''));

        $categories = Category::withCount('products')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'keyword' => $keyword,
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        if ($category->name !== $data['name']) {
            $data['slug'] = $category->makeUniqueSlug($data['name']);
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->hasProducts()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Kategori "' . $category->name . '" tidak bisa dihapus karena masih memiliki produk.');
        }

        try {
            $category->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Kategori "' . $category->name . '" tidak bisa dihapus karena masih berelasi dengan data lain.');
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}