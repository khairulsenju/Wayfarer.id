<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - Wayfarer1</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen flex-col bg-gray-50 text-gray-800">
    <nav class="border-b bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('catalog.index') }}" class="text-xl font-bold text-green-700">Wayfarer1</a>

            <div class="flex flex-wrap items-center gap-4 text-sm">
                <a href="{{ route('catalog.index') }}" class="hover:text-green-700">Katalog</a>

                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-green-700">Dashboard Admin</a>
                    @else
                        <a href="{{ route('cart.index') }}" class="hover:text-green-700">
                            Keranjang
                            @if (($cartCount ?? 0) > 0)
                                <span class="ml-1 rounded-full bg-green-600 px-2 py-0.5 text-xs text-white">{{ $cartCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('orders.index') }}" class="hover:text-green-700">Pesanan Saya</a>
                    @endif

                    <span class="text-gray-500">{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded border px-3 py-1 hover:bg-gray-100">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-green-700">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6">
        @include('partials.flash')

        @yield('content')
    </main>

    <footer class="border-t bg-white py-4 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} Wayfarer1. Jual dan sewa perlengkapan outdoor.
    </footer>
</body>
</html>