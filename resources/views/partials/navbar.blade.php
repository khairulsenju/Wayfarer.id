@php
    $isHome = request()->routeIs('home');
    $isCatalog = request()->routeIs('catalog.*');
    $isExplore = request()->routeIs('explore');
@endphp

<nav class="sticky top-0 z-50 border-b border-sage/30 bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-5 py-4">

        <div class="flex items-center justify-between gap-4">

            {{-- Logo --}}
            <a href="{{ route('home') }}"
               class="shrink-0 text-2xl font-extrabold tracking-tight text-forest">
                WAYFARER<span class="text-olive">.</span>
            </a>


            {{-- Desktop Navigation --}}
            <div class="hidden items-center gap-1 md:flex">

                <a href="{{ route('home') }}"
                   @class([
                       'rounded-full px-4 py-2 text-sm transition',
                       'bg-light-sage font-semibold text-forest' => $isHome,
                       'font-medium text-dark hover:bg-light-sage hover:text-forest' => ! $isHome,
                   ])>
                    Home
                </a>

                <a href="{{ route('catalog.index') }}"
                   @class([
                       'rounded-full px-4 py-2 text-sm transition',
                       'bg-light-sage font-semibold text-forest' => $isCatalog,
                       'font-medium text-dark hover:bg-light-sage hover:text-forest' => ! $isCatalog,
                   ])>
                    Katalog
                </a>

                <a href="{{ route('explore') }}"
                   @class([
                       'rounded-full px-4 py-2 text-sm transition',
                       'bg-light-sage font-semibold text-forest' => $isExplore,
                       'font-medium text-dark hover:bg-light-sage hover:text-forest' => ! $isExplore,
                   ])>
                    Explore Gear
                </a>

            </div>


            {{-- User Area --}}
            <div class="flex items-center gap-2 text-sm">

                @auth

                    @if (auth()->user()->isAdmin())

                        <a href="{{ route('admin.dashboard') }}"
                           class="rounded-full bg-forest px-4 py-2 font-semibold text-white transition hover:bg-forest-dark">
                            Dashboard Admin
                        </a>

                    @else

                        <a href="{{ route('cart.index') }}"
                           class="relative rounded-full p-2 text-forest transition hover:bg-light-sage"
                           aria-label="Keranjang">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 width="21"
                                 height="21"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>

                            </svg>

                            @if (($cartCount ?? 0) > 0)
                                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-olive px-1 text-xs font-bold text-white">
                                    {{ $cartCount }}
                                </span>
                            @endif

                        </a>

                        <a href="{{ route('orders.index') }}"
                           class="hidden rounded-full px-3 py-2 font-medium text-dark transition hover:bg-light-sage sm:block">
                            Pesanan Saya
                        </a>

                    @endif

                    <span class="hidden text-sm text-dark/60 lg:block">
                        Halo, {{ auth()->user()->name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                                class="rounded-full border border-forest px-4 py-2 font-semibold text-forest transition hover:bg-forest hover:text-white">
                            Keluar
                        </button>
                    </form>

                @else

                    <a href="{{ route('login') }}"
                       class="rounded-full px-4 py-2 font-semibold text-forest transition hover:bg-light-sage">
                        Masuk
                    </a>

                    <a href="{{ route('register') }}"
                       class="rounded-full bg-forest px-5 py-2 font-semibold text-white transition hover:bg-forest-dark">
                        Daftar
                    </a>

                @endauth

            </div>

        </div>


        {{-- Mobile Navigation --}}
        <div class="mt-3 flex items-center gap-1 overflow-x-auto border-t border-sage/20 pt-3 md:hidden">

            <a href="{{ route('home') }}"
               @class([
                   'shrink-0 rounded-full px-4 py-2 text-sm transition',
                   'bg-light-sage font-semibold text-forest' => $isHome,
                   'font-medium text-dark hover:bg-light-sage' => ! $isHome,
               ])>
                Home
            </a>

            <a href="{{ route('catalog.index') }}"
               @class([
                   'shrink-0 rounded-full px-4 py-2 text-sm transition',
                   'bg-light-sage font-semibold text-forest' => $isCatalog,
                   'font-medium text-dark hover:bg-light-sage' => ! $isCatalog,
               ])>
                Katalog
            </a>

            <a href="{{ route('explore') }}"
               @class([
                   'shrink-0 rounded-full px-4 py-2 text-sm transition',
                   'bg-light-sage font-semibold text-forest' => $isExplore,
                   'font-medium text-dark hover:bg-light-sage' => ! $isExplore,
               ])>
                Explore Gear
            </a>

        </div>

    </div>
</nav>