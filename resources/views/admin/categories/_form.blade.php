@php
    $cat = $category ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="max-w-xl space-y-4 rounded-lg border bg-white p-4">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="mb-1 block text-sm font-medium">Nama Kategori</label>
        <input type="text" id="name" name="name" value="{{ old('name', $cat->name ?? '') }}" required maxlength="100"
               class="w-full rounded border px-3 py-2">
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium">Deskripsi</label>
        <textarea id="description" name="description" rows="4" maxlength="1000"
                  class="w-full rounded border px-3 py-2">{{ old('description', $cat->description ?? '') }}</textarea>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Simpan</button>
        <a href="{{ route('admin.categories.index') }}" class="rounded border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
</form>