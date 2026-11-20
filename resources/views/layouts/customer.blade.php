
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Beranda') - Wayfarer</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

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

        /* NAVBAR WAYFARER */
        .wayfarer-navbar {
            background-color: #244B38;
            color: #F4F1E8;
            border-color: #45634F;
        }

        /* Background navbar */
        .wayfarer-navbar > * {
            background-color: transparent !important;
        }

        .wayfarer-navbar nav,
        .wayfarer-navbar header {
            background-color: transparent !important;
            border-color: #45634F !important;
        }

        /* Tulisan dan ikon navbar */
        .wayfarer-navbar a,
        .wayfarer-navbar button,
        .wayfarer-navbar span,
        .wayfarer-navbar p {
            color: #F4F1E8 !important;
        }

        .wayfarer-navbar svg {
            color: #F4F1E8 !important;
        }

        /* Hover navigasi */
        .wayfarer-navbar a:hover,
        .wayfarer-navbar button:hover {
            color: #C8D8C3 !important;
        }

        /* Navbar aktif */
        .wayfarer-navbar a.bg-light-sage {
            background-color: #DCE8D9 !important;
            color: #244B38 !important;
        }

        /* Tombol keluar */
        .wayfarer-navbar button {
            border-color: #F4F1E8;
        }

        /* Badge keranjang */
        .wayfarer-navbar .rounded-full {
            /* Tetap mengikuti bentuk asli */
        }

        /* VERIFIKASI PENYEWAAN */
        .wayfarer-verification {
            background-color: #244B38;
            border-color: #45634F;
        }

        .wayfarer-verification a {
            color: #F4F1E8;
            transition: color 0.2s ease;
        }

        .wayfarer-verification a:hover {
            color: #C8D8C3;
        }
    </style>

</head>

<body class="flex min-h-screen flex-col bg-cream text-dark antialiased">

    {{-- NAVBAR --}}
    <div class="wayfarer-navbar">
        @include('partials.navbar')
    </div>

    {{-- VERIFIKASI PENYEWAAN --}}
    @if(auth()->check() && auth()->user()->isCustomer())

        <div class="wayfarer-verification border-b">
            <div class="mx-auto flex max-w-7xl justify-end px-6 py-2">

                <a
                    href="{{ route('rental-verification.index') }}"
                    class="text-sm font-medium"
                >
                    Verifikasi Penyewaan →
                </a>

            </div>
        </div>

    @endif

    {{-- MAIN CONTENT --}}
    <main class="w-full flex-1">

        @include('partials.flash')

        @yield('content')

    </main>

    {{-- FOOTER --}}
    @include('partials.footer')

    @stack('scripts')

</body>
</html>
