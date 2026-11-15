@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Kategori</h1>
        <a href="{{ route('admin.categories.create') }}" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Tambah Kategori</a>
    </div>

    <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="q" value="{{ $keyword }}" placeholder="Cari kategori..." class="w-full max-w-xs rounded border px-3 py-2">
        <button type="submit" class="rounded border bg-white px-4 py-2 hover:bg-gray-50">Cari</button>
        <a href="{{ route('admin.categories.index') }}" class="rounded border bg-white px-4 py-2 hover:bg-gray-50">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-3 py-2">Nama</th>
                    <th class="px-3 py-2">Slug</th>
                    <th class="px-3 py-2">Deskripsi</th>
                    <th class="px-3 py-2 text-right">Jumlah Produk</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr class="border-t align-top">
                        <td class="px-3 py-2 font-medium">{{ $category->name }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $category->slug }}</td>
                        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($category->description, 80) }}</td>
                        <td class="px-3 py-2 text-right">{{ $category->products_count }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="mr-3 text-green-700 hover:underline">Ubah</a>

                            @if ($category->products_count > 0)
                                <span class="cursor-not-allowed text-gray-400" title="Kategori masih memiliki produk">Hapus</span>
                            @else
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline"
                                      onsubmit="return confirm('Hapus kategori {{ e($category->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-gray-500">Kategori tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
@endsection