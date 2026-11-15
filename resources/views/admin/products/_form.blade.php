@php
    $p = $product ?? null;
    $currentMode = old('mode', $p->mode ?? 'sale');
    $isActive = old('is_active', $p ? ($p->is_active ? 1 : 0) : 1);
    $modeLabels = ['sale' => 'Jual', 'rent' => 'Sewa', 'both' => 'Jual & Sewa'];
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="max-w-3xl space-y-4 rounded-lg border bg-white p-4">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Nama Produk</label>
            <input type="text" id="name" name="name" value="{{ old('name', $p->name ?? '') }}" required maxlength="150"
                   class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label for="category_id" class="mb-1 block text-sm font-medium">Kategori</label>
            <select id="category_id" name="category_id" required class="w-full rounded border px-3 py-2">
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $p->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="mode" class="mb-1 block text-sm font-medium">Mode Produk</label>
            <select id="mode" name="mode" required class="w-full rounded border px-3 py-2">
                @foreach ($modes as $value)
                    <option value="{{ $value }}" @selected($currentMode === $value)>{{ $modeLabels[$value] ?? $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked((int) $isActive === 1) class="rounded border">
                <label for="is_active" class="text-sm">Produk aktif (tampil di katalog)</label>
            </div>
        </div>
    </div>

    <div id="sale-fields" class="grid gap-4 rounded border bg-blue-50 p-3 md:grid-cols-2">
        <div class="font-semibold md:col-span-2">Pengaturan Jual</div>

        <div>
            <label for="sale_price" class="mb-1 block text-sm font-medium">Harga Jual (Rp)</label>
            <input type="number" id="sale_price" name="sale_price" min="0" value="{{ old('sale_price', $p->sale_price ?? '') }}"
                   class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label for="stock_sale" class="mb-1 block text-sm font-medium">Stok Jual</label>
            <input type="number" id="stock_sale" name="stock_sale" min="0" value="{{ old('stock_sale', $p->stock_sale ?? '') }}"
                   class="w-full rounded border px-3 py-2">
        </div>
    </div>

    <div id="rent-fields" class="grid gap-4 rounded border bg-orange-50 p-3 md:grid-cols-3">
        <div class="font-semibold md:col-span-3">Pengaturan Sewa</div>

        <div>
            <label for="rent_price_per_day" class="mb-1 block text-sm font-medium">Harga Sewa per Hari (Rp)</label>
            <input type="number" id="rent_price_per_day" name="rent_price_per_day" min="0"
                   value="{{ old('rent_price_per_day', $p->rent_price_per_day ?? '') }}" class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label for="deposit" class="mb-1 block text-sm font-medium">Deposit per Unit (Rp)</label>
            <input type="number" id="deposit" name="deposit" min="0" value="{{ old('deposit', $p->deposit ?? '') }}"
                   class="w-full rounded border px-3 py-2">
        </div>

        <div>
            <label for="stock_rent" class="mb-1 block text-sm font-medium">Stok Sewa</label>
            <input type="number" id="stock_rent" name="stock_rent" min="0" value="{{ old('stock_rent', $p->stock_rent ?? '') }}"
                   class="w-full rounded border px-3 py-2">
        </div>
    </div>

    <div>
        <label for="image" class="mb-1 block text-sm font-medium">Gambar (jpg, jpeg, png, webp, maks 2 MB)</label>

        @if ($p && $p->image)
            <div class="mb-2 flex items-center gap-4">
                <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="h-24 w-32 rounded border object-cover">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remove_image" value="1" class="rounded border">
                    Hapus gambar saat ini
                </label>
            </div>
        @endif

        <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp" class="w-full rounded border px-3 py-2">
    </div>

    <div class="flex gap-2">
        <button type="submit" class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Simpan</button>
        <a href="{{ route('admin.products.index') }}" class="rounded border px-4 py-2 hover:bg-gray-50">Batal</a>
    </div>
</form>

<script>
    (function () {
        var modeSelect = document.getElementById('mode');
        var saleBox = document.getElementById('sale-fields');
        var rentBox = document.getElementById('rent-fields');

        function toggle(box, show) {
            box.style.display = show ? '' : 'none';

            box.querySelectorAll('input').forEach(function (input) {
                input.disabled = !show;
            });
        }

        function apply() {
            var mode = modeSelect.value;

            toggle(saleBox, mode === 'sale' || mode === 'both');
            toggle(rentBox, mode === 'rent' || mode === 'both');
        }

        modeSelect.addEventListener('change', apply);
        apply();
    })();
</script>