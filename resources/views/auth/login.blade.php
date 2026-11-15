@extends('layouts.customer')

@section('title', 'Masuk')

@section('content')
    <div class="mx-auto max-w-md rounded-lg border bg-white p-6">
        <h1 class="mb-4 text-2xl font-bold">Masuk</h1>

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded border px-3 py-2">
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full rounded border px-3 py-2">
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="remember" value="0">
                <input type="checkbox" id="remember" name="remember" value="1" class="rounded border">
                <label for="remember" class="text-sm">Ingat saya</label>
            </div>

            <button type="submit" class="w-full rounded bg-green-600 py-2 font-medium text-white hover:bg-green-700">Masuk</button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-green-700 hover:underline">Daftar sekarang</a>
        </p>
    </div>
@endsection