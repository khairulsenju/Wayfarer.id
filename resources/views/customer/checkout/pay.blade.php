@extends('layouts.customer')

@section('title', 'Pembayaran')

@section('content')
    <div class="mx-auto max-w-md rounded-lg border bg-white p-6 text-center">
        <h1 class="mb-2 text-2xl font-bold">Pembayaran</h1>
        <div class="text-sm text-gray-600">Kode pesanan</div>
        <div class="mb-3 font-mono font-semibold">{{ $order->order_code }}</div>

        <div class="text-sm text-gray-600">Total yang harus dibayar</div>
        <div class="mb-4 text-3xl font-bold text-green-700">@rupiah($order->grand_total)</div>

        <p class="mb-4 text-sm text-gray-500">
            Jendela pembayaran Midtrans akan terbuka otomatis. Jika tidak muncul, klik tombol di bawah.
        </p>

        <button type="button" id="pay-button" class="w-full rounded bg-green-600 py-2 font-medium text-white hover:bg-green-700">
            Bayar Sekarang
        </button>

        <a href="{{ $orderUrl }}" class="mt-3 block text-sm text-gray-600 hover:underline">Bayar nanti, lihat detail pesanan</a>
    </div>

    <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script>
        (function () {
            var snapToken = @json($snapToken);
            var finishUrl = @json($finishUrl);
            var orderUrl = @json($orderUrl);

            function openSnap() {
                window.snap.pay(snapToken, {
                    onSuccess: function () {
                        window.location.href = finishUrl;
                    },
                    onPending: function () {
                        window.location.href = finishUrl;
                    },
                    onError: function () {
                        window.location.href = orderUrl;
                    },
                    onClose: function () {
                        window.location.href = orderUrl;
                    }
                });
            }

            document.getElementById('pay-button').addEventListener('click', openSnap);

            window.addEventListener('load', openSnap);
        })();
    </script>
@endsection