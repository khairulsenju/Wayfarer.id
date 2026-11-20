
@extends('layouts.admin')

@section('title', 'Verifikasi Penyewa')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        Verifikasi Penyewa
    </h1>
    <p class="mt-1 text-sm text-gray-500">
        Periksa identitas pelanggan sebelum memberikan akses penyewaan.
    </p>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="mb-1 font-semibold">Periksa kembali data:</p>
        <ul class="list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- FILTER STATUS --}}
<div class="mb-5 flex flex-wrap gap-2">

    <a href="{{ route('admin.rental-verifications.index') }}"
       class="rounded-lg border px-4 py-2 text-sm transition
       {{ !request()->has('status') ? 'border-green-700 bg-green-700 text-white' : 'border-gray-200 bg-white hover:bg-gray-50' }}">
        Semua
    </a>

    @foreach (['pending', 'approved', 'rejected'] as $status)
        <a href="{{ route('admin.rental-verifications.index', ['status' => $status]) }}"
           class="rounded-lg border px-4 py-2 text-sm transition
           {{ request('status') === $status ? 'border-green-700 bg-green-700 text-white' : 'border-gray-200 bg-white hover:bg-gray-50' }}">
            {{ ucfirst($status) }}
        </a>
    @endforeach

</div>

{{-- DAFTAR VERIFIKASI --}}
<div class="space-y-5">

    @forelse ($verifications as $verification)

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

            {{-- INFORMASI PELANGGAN --}}
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">

                <div>
                    <h2 class="font-bold text-gray-900">
                        {{ $verification->user->name }}
                    </h2>

                    <p class="text-sm text-gray-500">
                        {{ $verification->user->email }}
                    </p>

                    <p class="text-sm text-gray-500">
                        {{ $verification->user->phone ?? '-' }}
                    </p>
                </div>

                {{-- BADGE STATUS --}}
                <div>
                    @if ($verification->status === 'pending')
                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                            Pending
                        </span>

                    @elseif ($verification->status === 'approved')
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                            Approved
                        </span>

                    @else
                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                            Rejected
                        </span>
                    @endif
                </div>

            </div>

            {{-- FOTO KTP --}}
            <a href="{{ route('admin.rental-verifications.ktp', $verification) }}"
               target="_blank"
               rel="noopener"
               class="mb-4 inline-block rounded-lg border border-green-700 px-4 py-2 text-sm font-medium text-green-700 transition hover:bg-green-50">
                Lihat Foto KTP
            </a>

            {{-- STATUS PENDING --}}
            @if ($verification->status === 'pending')

                <div class="space-y-3">

                    {{-- FORM SETUJUI --}}
                    <form method="POST"
                          action="{{ route('admin.rental-verifications.approve', $verification) }}"
                          class="space-y-3 rounded-lg bg-green-50 p-4">

                        @csrf

                        <h3 class="font-semibold text-green-900">
                            Setujui Verifikasi
                        </h3>

                        <label class="block text-sm font-medium text-gray-700">
                            Catatan Admin (opsional)
                        </label>

                        <textarea
                            name="admin_notes"
                            rows="2"
                            class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600"
                        >{{ old('admin_notes') }}</textarea>

                        <label class="flex items-start gap-2 text-sm text-gray-700">
                            <input
                                type="checkbox"
                                name="phone_confirmation"
                                value="1"
                                required
                                class="mt-1"
                            >

                            <span>
                                Saya sudah menghubungi pelanggan,
                                mencocokkan nomor telepon, dan memeriksa
                                kesesuaian identitas KTP.
                            </span>
                        </label>

                        <button
                            type="submit"
                            class="rounded-lg bg-green-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                            Setujui Verifikasi
                        </button>

                    </form>

                    {{-- FORM TOLAK --}}
                    <form method="POST"
                          action="{{ route('admin.rental-verifications.reject', $verification) }}"
                          class="space-y-3 rounded-lg bg-red-50 p-4">

                        @csrf

                        <h3 class="font-semibold text-red-900">
                            Tolak Verifikasi
                        </h3>

                        <textarea
                            name="admin_notes"
                            rows="2"
                            required
                            placeholder="Tuliskan alasan penolakan..."
                            class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                        >{{ old('admin_notes') }}</textarea>

                        <button
                            type="submit"
                            class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                            Tolak Pengajuan
                        </button>

                    </form>

                </div>

            {{-- STATUS APPROVED --}}
            @elseif ($verification->status === 'approved')

                <div class="rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-green-800">

                    <p class="font-semibold">
                        ✓ Verifikasi Berhasil Disetujui
                    </p>

                    <p class="mt-2">
                        Pelanggan sudah dapat melakukan penyewaan perlengkapan Wayfarer.
                    </p>

                    <p class="mt-2 text-xs text-green-700">
                        Disetujui pada:
                        {{ $verification->reviewed_at?->format('d/m/Y H:i') ?? '-' }}
                    </p>

                    @if ($verification->admin_notes)
                        <div class="mt-3 border-t border-green-200 pt-3">
                            <p class="font-semibold">Catatan Admin:</p>
                            <p class="mt-1">{{ $verification->admin_notes }}</p>
                        </div>
                    @endif

                </div>

            {{-- STATUS REJECTED --}}
            @elseif ($verification->status === 'rejected')

                <div class="rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-800">

                    <p class="font-semibold">
                        ✕ Verifikasi Ditolak
                    </p>

                    <p class="mt-2">
                        Pengajuan verifikasi identitas pelanggan belum dapat disetujui.
                    </p>

                    <div class="mt-3 rounded-lg bg-white p-3">
                        <p class="font-semibold">
                            Alasan Penolakan:
                        </p>

                        <p class="mt-1">
                            {{ $verification->admin_notes ?: 'Tidak ada catatan penolakan.' }}
                        </p>
                    </div>

                    <p class="mt-3 text-xs text-red-700">
                        Ditolak pada:
                        {{ $verification->reviewed_at?->format('d/m/Y H:i') ?? '-' }}
                    </p>

                </div>

            @endif

        </div>

    @empty

        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center text-gray-500">
            Belum ada pengajuan verifikasi pelanggan.
        </div>

    @endforelse

</div>

{{-- PAGINATION --}}
<div class="mt-5">
    {{ $verifications->links() }}
</div>

@endsection
