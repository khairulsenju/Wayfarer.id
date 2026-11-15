@extends('layouts.customer')

@section('title', 'Daftar')

@section('content')
    <div class="mx-auto max-w-md rounded-lg border bg-white p-6">
        <h1 class="mb-4 text-2xl font-bold">Daftar Akun</h1>

        <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-medium">Nama</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full rounded border px-3 py-2">
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded border px-3 py-2">
            </div>

            <div>
                <label for="phone" class="mb-1 block text-sm font-medium">Nomor Telepon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                       class="w-full rounded border px-3 py-2">
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full rounded border px-3 py-2">
                <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter.</p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full rounded border px-3 py-2">
            </div>

            <button type="submit" class="w-full rounded bg-green-600 py-2 font-medium text-white hover:bg-green-700">Daftar</button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-green-700 hover:underline">Masuk</a>
        </p>
    </div>
@endsection