<footer class="bg-primary-container text-white pt-16 pb-8">
    <div class="max-w-[1280px] mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-12 border-b border-white/10 pb-12 mb-8">
        <div>
            <h3 class="font-serif text-2xl font-bold text-secondary-container mb-4">Saung Babakan Cinta</h3>
            <p class="text-sm text-white/70 leading-relaxed">
                Menyajikan hidangan Sunda otentik dengan cita rasa tradisional yang dipadukan dengan pelayanan premium untuk setiap momen spesial Anda.
            </p>
        </div>
        <div>
            <h4 class="font-bold mb-4 uppercase tracking-wider text-sm text-secondary-container">Layanan Kami</h4>
            <ul class="space-y-3 text-sm text-white/70">
                <li><a href="{{ route('home') }}#menu-dinein" class="hover:text-secondary-container transition-colors">Menu</a></li>
                <li><a href="{{ route('home') }}#catering" class="hover:text-secondary-container transition-colors">Katering</a></li>
                <li><a href="{{ route('home') }}#nasi-box" class="hover:text-secondary-container transition-colors">Nasi Box</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold mb-4 uppercase tracking-wider text-sm text-secondary-container">Hubungi Kami</h4>
            <ul class="space-y-3 text-sm text-white/70">
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-secondary-container" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                   Jl. Ciloa No.km 6, Pasirhalang, Kec. Cisarua, Kabupaten Bandung Barat, Jawa Barat 40551
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-secondary-container" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    WA: +62 813-9461-6635
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-secondary-container" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    IG: @saungbabakancinta
                </li>
            </ul>
        </div>
    </div>
    <div class="max-w-[1280px] mx-auto px-6 flex flex-col md:flex-row items-center justify-between text-xs text-white/40 gap-4">
        <div>
            &copy; {{ date('Y') }} Saung Babakan Cinta. Seluruh hak cipta dilindungi undang-undang.
        </div>
    </div>
</footer>
