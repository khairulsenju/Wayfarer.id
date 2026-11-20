<footer class="bg-forest text-white">

    <div class="mx-auto grid max-w-7xl gap-10 px-6 py-12 md:grid-cols-3">

        {{-- Brand --}}
        <div>
            <h2 class="mb-3 text-2xl font-extrabold tracking-wide">
                WAYFARER<span class="text-sage">.</span>
            </h2>

            <p class="max-w-sm text-sm leading-7 text-white/65">
                Temukan perlengkapan terbaik untuk setiap petualangan.
                Beli atau sewa perlengkapan outdoor dengan lebih mudah
                bersama Wayfarer.
            </p>
        </div>


        {{-- Navigasi --}}
        <div>
            <h3 class="mb-4 font-bold">
                Navigasi
            </h3>

            <ul class="space-y-3 text-sm text-white/65">

                <li>
                    <a href="{{ route('home') }}"
                       class="transition hover:text-white">
                        Beranda
                    </a>
                </li>

                <li>
                    <a href="{{ route('explore') }}"
                       class="transition hover:text-white">
                        Explore Gear
                    </a>
                </li>

                <li>
                    <a href="{{ route('catalog.index') }}"
                       class="transition hover:text-white">
                        Katalog Produk
                    </a>
                </li>

                @auth
                    @if (!auth()->user()->isAdmin())
                        <li>
                            <a href="{{ route('orders.index') }}"
                               class="transition hover:text-white">
                                Pesanan Saya
                            </a>
                        </li>
                    @endif
                @endauth

            </ul>
        </div>


        {{-- Informasi --}}
        <div>
            <h3 class="mb-4 font-bold">
                Wayfarer
            </h3>

            <p class="text-sm leading-7 text-white/65">
                Platform jual beli dan sewa perlengkapan outdoor
                untuk menemani perjalanan dan petualanganmu.
            </p>
        </div>

    </div>


    <div class="border-t border-white/10 px-6 py-5 text-center text-xs text-white/50">
        &copy; {{ date('Y') }} Wayfarer.
        All Rights Reserved.
    </div>

</footer>