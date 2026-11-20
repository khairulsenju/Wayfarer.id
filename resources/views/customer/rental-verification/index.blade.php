@extends('layouts.customer')

@section('title', 'Verifikasi Penyewaan')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">

    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-wider text-green-700">
            Wayfarer Rental
        </p>

        <h1 class="mt-2 text-3xl font-bold text-gray-900">
            Verifikasi Identitas
        </h1>

        <p class="mt-2 text-sm text-gray-600">
            Verifikasi diperlukan sebelum kamu dapat menyewa perlengkapan outdoor.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 rounded-xl border bg-white p-5">

        <h2 class="mb-3 font-semibold">
            Status Verifikasi
        </h2>

        @if (!$verification)
            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm">
                Belum Mengajukan
            </span>

            <p class="mt-3 text-sm text-gray-600">
                Silakan lengkapi data identitas di bawah.
            </p>

        @elseif ($verification->status === 'pending')
            <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm text-yellow-800">
                Menunggu Pemeriksaan
            </span>

            <p class="mt-3 text-sm text-gray-600">
                Dokumen sedang diperiksa oleh admin.
                Kamu belum dapat melakukan penyewaan.
            </p>

        @elseif ($verification->status === 'approved' && $verification->isApproved())
            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-800">
                Terverifikasi
            </span>

            <p class="mt-3 text-sm text-green-700">
                Identitas kamu telah disetujui.
                Sekarang kamu dapat menyewa perlengkapan Wayfarer.
            </p>

        @else
            <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-800">
                Pengajuan Ditolak
            </span>

            <p class="mt-3 text-sm text-gray-600">
                Alasan:
                {{ $verification->admin_notes ?? 'Silakan periksa kembali dokumen.' }}
            </p>

            <p class="mt-2 text-sm text-gray-600">
                Silakan unggah ulang dokumen yang sesuai.
            </p>
        @endif

    </div>

    @if (!$verification || !$verification->isApproved())

        <form
            method="POST"
            action="{{ route('rental-verification.store') }}"
            enctype="multipart/form-data"
            class="space-y-5 rounded-xl border bg-white p-6"
        >
            @csrf

            <h2 class="text-lg font-semibold">
                Lengkapi Data Identitas
            </h2>

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    value="{{ $user->name }}"
                    readonly
                    class="w-full rounded-lg border bg-gray-100 px-4 py-3"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Email
                </label>

                <input
                    type="email"
                    value="{{ $user->email }}"
                    readonly
                    class="w-full rounded-lg border bg-gray-100 px-4 py-3"
                >
            </div>

            <div>
                <label for="phone" class="mb-2 block text-sm font-medium">
                    Nomor Telepon Aktif
                </label>

                <input
                    type="text"
                    name="phone"
                    id="phone"
                    value="{{ old('phone', $user->phone) }}"
                    required
                    placeholder="Contoh: 081234567890"
                    class="w-full rounded-lg border px-4 py-3"
                >

                <p class="mt-1 text-xs text-gray-500">
                    Admin akan menghubungi nomor ini untuk konfirmasi identitas.
                </p>
            </div>

            <div>
                <label for="ktp" class="mb-2 block text-sm font-medium">
                    Foto KTP
                </label>

                <input
                    type="file"
                    name="ktp"
                    id="ktp"
                    accept=".jpg,.jpeg,.png"
                    required
                    class="w-full rounded-lg border p-3"
                >

                <p class="mt-1 text-xs text-gray-500">
                    Format JPG, JPEG, PNG. Maksimal 5 MB.
                    Pastikan foto jelas dan tidak terpotong.
                </p>
            </div>

            <div class="rounded-lg bg-yellow-50 p-4 text-sm text-yellow-900">
                <strong>Perlindungan Data:</strong>

                Foto KTP hanya digunakan untuk pemeriksaan identitas
                penyewa dan hanya dapat diakses oleh admin yang berwenang.
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-green-800 px-5 py-3 font-semibold text-white hover:bg-green-900"
            >
                Kirim Dokumen Verifikasi
            </button>

        </form>

    @endif

</div>
@endsection