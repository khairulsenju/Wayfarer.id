@extends('layouts.admin')

@section('title', 'Produk')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Produk</h1>
        <a href="{{ route('admin.products.create') }}" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Tambah Produk</a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-4 grid gap-3 rounded-lg border bg-white p-3 md:grid-cols-5">
        <input type="text" name="q" value="{{ $keyword }}" placeholder="Cari produk..." class="rounded border px-3 py-2">

        <select name="category_id" class="rounded border px-3 py-2">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>

        <select name="mode" class="rounded border px-3 py-2">
            <option value="">Semua mode</option>
            <option value="sale" @selected($mode === 'sale')>Jual</option>
            <option value="rent" @selected($mode === 'rent')>Sewa</option>
            <option value="both" @selected($mode === 'both')>Jual & Sewa</option>
        </select>

        <select name="status" class="rounded border px-3 py-2">
            <option value="">Semua status</option>
            <option value="active" @selected($status === 'active')>Aktif</option>
            <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Filter</button>
            <a href="{{ route('admin.products.index') }}" class="rounded border px-4 py-2 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-3 py-2">Produk</th>
                    <th class="px-3 py-2">Kategori</th>
                    <th class="px-3 py-2">Mode</th>
                    <th class="px-3 py-2 text-right">Harga Jual</th>
                    <th class="px-3 py-2 text-right">Sewa/Hari</th>
                    <th class="px-3 py-2 text-right">Deposit</th>
                    <th class="px-3 py-2 text-right">Stok Jual</th>
                    <th class="px-3 py-2 text-right">Stok Sewa</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t align-top">
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-10 w-14 rounded border object-cover">
                                <span class="font-medium">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2">{{ $product->category->name }}</td>
                        <td class="px-3 py-2"><x-badge :status="$product->mode">{{ $product->modeLabel() }}</x-badge></td>
                        <td class="px-3 py-2 text-right">{{ $product->canBeSold() ? \App\Services\PricingService::rupiah($product->sale_price) : '-' }}</td>
                        <td class="px-3 py-2 text-right">{{ $product->canBeRented() ? \App\Services\PricingService::rupiah($product->rent_price_per_day) : '-' }}</td>
                        <td class="px-3 py-2 text-right">{{ $product->canBeRented() ? \App\Services\PricingService::rupiah($product->deposit) : '-' }}</td>
                        <td class="px-3 py-2 text-right">{{ $product->canBeSold() ? $product->stock_sale : '-' }}</td>
                        <td class="px-3 py-2 text-right">{{ $product->canBeRented() ? $product->stock_rent : '-' }}</td>
                        <td class="px-3 py-2">
                            <x-badge :status="$product->is_active ? 'active' : 'inactive'">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="mr-3 text-green-700 hover:underline">Ubah</a>

                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline"
                                  onsubmit="return confirm('Hapus produk {{ e($product->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-3 py-6 text-center text-gray-500">Produk tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection