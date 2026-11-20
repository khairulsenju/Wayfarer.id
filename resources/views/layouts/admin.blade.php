
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - Admin Wayfarer</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'DM Sans', sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">

    @php
        $menus = [
            [
                'route' => 'admin.dashboard',
                'pattern' => 'admin.dashboard',
                'label' => 'Dashboard'
            ],
            [
                'route' => 'admin.categories.index',
                'pattern' => 'admin.categories.*',
                'label' => 'Kategori'
            ],
            [
                'route' => 'admin.products.index',
                'pattern' => 'admin.products.*',
                'label' => 'Produk'
            ],
            [
                'route' => 'admin.orders.index',
                'pattern' => 'admin.orders.*',
                'label' => 'Pesanan'
            ],
            [
                'route' => 'admin.processing.index',
                'pattern' => 'admin.processing.*',
                'label' => 'Pemrosesan'
            ],
            [
                'route' => 'admin.rental-verifications.index',
                'pattern' => 'admin.rental-verifications.*',
                'label' => 'Verifikasi Penyewa'
            ],
            [
                'route' => 'admin.returns.index',
                'pattern' => 'admin.returns.*',
                'label' => 'Pengembalian Sewa'
            ],
            [
                'route' => 'admin.finance.index',
                'pattern' => 'admin.finance.*',
                'label' => 'Keuangan & Laporan'
            ],
        ];
    @endphp

    <div class="flex min-h-screen flex-col md:flex-row">

        {{-- SIDEBAR --}}
        <aside class="w-full bg-gray-900 text-gray-100 md:w-60 md:shrink-0">

            <div class="px-4 py-4 text-xl font-bold text-green-400">
                Wayfarer Admin
            </div>

            {{-- NAVIGATION --}}
            <nav class="flex flex-wrap gap-1 px-2 pb-2 md:block md:space-y-1">

                @foreach ($menus as $menu)

                    <a
                        href="{{ route($menu['route']) }}"
                        class="block rounded px-3 py-2 text-sm transition
                        {{ request()->routeIs($menu['pattern'])
                            ? 'bg-green-600 text-white font-semibold'
                            : 'text-gray-100 hover:bg-gray-800' }}"
                    >
                        {{ $menu['label'] }}
                    </a>

                @endforeach

            </nav>

            {{-- ADMIN PROFILE --}}
            <div class="border-t border-gray-700 px-4 py-3 text-sm">

                <div class="mb-2 text-gray-400">
                    {{ auth()->user()->name }}
                </div>

                <a
                    href="{{ route('catalog.index') }}"
                    target="_blank"
                    class="mb-2 block hover:text-green-400"
                >
                    Lihat Toko
                </a>

                <form method="POST" action="{{ route('logout') }}">

                    @csrf

                    <button
                        type="submit"
                        class="rounded border border-gray-600 px-3 py-1 hover:bg-gray-800"
                    >
                        Keluar
                    </button>

                </form>

            </div>

        </aside>

        {{-- MAIN CONTENT --}}
        <main class="min-w-0 flex-1 p-4 md:p-6">

            @include('partials.flash')

            @yield('content')

        </main>

    </div>

</body>
</html>
