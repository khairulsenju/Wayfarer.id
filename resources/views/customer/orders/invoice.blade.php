<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_code }} - Wayfarer1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; }
        }
    </style>
</head>
<body class="bg-gray-100 p-6 text-gray-800">
    <div class="no-print mx-auto mb-4 flex max-w-3xl justify-between">
        <a href="{{ route('orders.show', $order) }}" class="text-sm text-green-700 hover:underline">&larr; Kembali</a>
        <button type="button" onclick="window.print()" class="rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">Cetak</button>
    </div>

    <div class="mx-auto max-w-3xl rounded-lg border bg-white p-8">
        <div class="mb-6 flex justify-between">
            <div>
                <div class="text-2xl font-bold text-green-700">Wayfarer1</div>
                <div class="text-sm text-gray-500">Jual dan sewa perlengkapan outdoor</div>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold">INVOICE</div>
                <div class="font-mono text-sm">{{ $order->order_code }}</div>
                <div class="text-sm text-gray-500">Tanggal bayar: {{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : '-' }}</div>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-6 text-sm">
            <div>
                <div class="mb-1 font-semibold">Ditagihkan kepada</div>
                <div>{{ $order->user->name }}</div>
                <div>{{ $order->user->email }}</div>
            </div>
            <div>
                <div class="mb-1 font-semibold">Dikirim ke</div>
                <div>{{ $order->recipient_name }}</div>
                <div>{{ $order->phone }}</div>
                <div class="whitespace-pre-line">{{ $order->address }}</div>
            </div>
        </div>

        <table class="mb-6 min-w-full text-sm">
            <thead class="border-b-2 text-left">
                <tr>
                    <th class="py-2">Produk</th>
                    <th class="py-2">Tipe</th>
                    <th class="py-2 text-right">Jumlah</th>
                    <th class="py-2 text-right">Harga</th>
                    <th class="py-2 text-right">Deposit</th>
                    <th class="py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-b align-top">
                        <td class="py-2">
                            {{ $item->product->name }}
                            @if ($item->type === 'rent')
                                <div class="text-xs text-gray-500">
                                    {{ $item->rent_start->format('d/m/Y') }} - {{ $item->rent_end->format('d/m/Y') }} ({{ $item->rent_days }} hari)
                                </div>
                            @endif
                        </td>
                        <td class="py-2">{{ $item->type === 'rent' ? 'Sewa' : 'Beli' }}</td>
                        <td class="py-2 text-right">{{ $item->qty }}</td>
                        <td class="py-2 text-right">
                            @rupiah($item->unit_price)
                            @if ($item->type === 'rent')
                                <div class="text-xs text-gray-500">per hari</div>
                            @endif
                        </td>
                        <td class="py-2 text-right">
                            @if ($item->deposit > 0)
                                @rupiah($item->deposit)
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-2 text-right">@rupiah($item->subtotal)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="ml-auto max-w-xs text-sm">
            <div class="flex justify-between py-1">
                <span>Subtotal</span>
                <span>@rupiah($order->subtotal)</span>
            </div>
            <div class="flex justify-between py-1">
                <span>Total Deposit</span>
                <span>@rupiah($order->deposit_total)</span>
            </div>
            <div class="flex justify-between border-t-2 py-2 text-lg font-bold">
                <span>Total Dibayar</span>
                <span>@rupiah($order->grand_total)</span>
            </div>
            <div class="text-right text-xs text-gray-500">
                Metode: {{ $order->payment && $order->payment->payment_type ? $order->payment->payment_type : '-' }}
            </div>
        </div>

        @if ($order->hasRentItems())
            <p class="mt-6 border-t pt-4 text-xs text-gray-500">
                Deposit sewa bersifat jaminan dan dikembalikan setelah barang sewaan diterima kembali dalam kondisi baik.
            </p>
        @endif
    </div>
</body>
</html>