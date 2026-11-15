@extends('layouts.customer')

@section('title', 'Checkout')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Checkout</h1>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="md:col-span-2">
            <form method="POST" action="{{ route('checkout.store') }}" class="space-y-4 rounded-lg border bg-white p-4">
                @csrf

                <h2 class="font-semibold">Data Penerima</h2>

                <div>
                    <label for="recipient_name" class="mb-1 block text-sm font-medium">Nama Penerima</label>
                    <input type="text" id="recipient_name" name="recipient_name"
                           value="{{ old('recipient_name', $user->name) }}" required class="w-full rounded border px-3 py-2">
                </div>

                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium">Nomor Telepon</label>
                    <input type="text" id="phone" name="phone"
                           value="{{ old('phone', $user->phone) }}" required class="w-full rounded border px-3 py-2">
                </div>

                <div>
                    <label for="address" class="mb-1 block text-sm font-medium">Alamat Lengkap</label>
                    <textarea id="address" name="address" rows="4" required class="w-full rounded border px-3 py-2">{{ old('address') }}</textarea>
                </div>

                <button type="submit" class="w-full rounded bg-green-600 py-2 font-medium text-white hover:bg-green-700">
                    Buat Pesanan dan Bayar
                </button>
            </form>
        </div>

        <div class="rounded-lg border bg-white p-4">
            <h2 class="mb-3 font-semibold">Ringkasan Pesanan</h2>

            <div class="space-y-3 text-sm">
                @foreach ($summary['lines'] as $line)
                    <div class="border-b pb-2">
                        <div class="font-medium">{{ $line['product']->name }}</div>

                        @if ($line['type'] === 'rent')
                            <div class="text-gray-600">
                                Sewa {{ $line['qty'] }} unit x {{ $line['rent_days'] }} hari
                                ({{ \Carbon\Carbon::parse($line['rent_start'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($line['rent_end'])->format('d/m/Y') }})
                            </div>
                            <div class="flex justify-between">
                                <span>Biaya sewa</span>
                                <span>@rupiah($line['subtotal'])</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Deposit</span>
                                <span>@rupiah($line['deposit'])</span>
                            </div>
                        @else
                            <div class="text-gray-600">Beli {{ $line['qty'] }} unit x @rupiah($line['unit_price'])</div>
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span>@rupiah($line['subtotal'])</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>@rupiah($summary['subtotal'])</span>
                </div>
                <div class="flex justify-between">
                    <span>Total Deposit</span>
                    <span>@rupiah($summary['deposit_total'])</span>
                </div>
                <div class="flex justify-between border-t pt-2 text-lg font-bold">
                    <span>Total Bayar</span>
                    <span>@rupiah($summary['grand_total'])</span>
                </div>
            </div>

            <a href="{{ route('cart.index') }}" class="mt-4 block text-center text-sm text-green-700 hover:underline">Ubah keranjang</a>
        </div>
    </div>
@endsection