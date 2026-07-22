<x-layouts.landing>
    <x-slot:title>Saung Babakan Cinta - Rumah Makan Sunda</x-slot:title>

    {{-- 1. HERO --}}
    <x-landing.hero />

    {{-- 2. TENTANG --}}
    <x-landing.section id="tentang" title="Tentang Kami">
        <div class="w-12 h-[1px] bg-secondary mx-auto mb-6"></div>
        <x-typography.p variant="large" class="text-center max-w-3xl mx-auto">Saung Babakan Cinta menyajikan hidangan khas
            Sunda dengan cita rasa otentik di Bandung. Kami melayani makan di tempat, catering untuk acara spesial, dan
            nasi box untuk berbagai kebutuhan.</x-typography.p>
    </x-landing.section>

    {{-- 3. LAYANAN --}}
    <x-landing.section title="Layanan Kami" bgImage="{{ asset('images/saungbabakan.webp') }}">
        <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <div class="bg-surface rounded-xl border border-primary/10 p-6 flex flex-col text-center">
                <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-6 h-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                </div>
                <x-typography.h3 class="text-primary mb-2">Dine-in</x-typography.h3>
                <x-typography.p variant="small">Makan di tempat dengan suasana saung yang nyaman.</x-typography.p>
            </div>
            <div class="bg-surface rounded-xl border border-primary/10 p-6 flex flex-col text-center">
                <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-6 h-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <x-typography.h3 class="text-primary mb-2">Catering</x-typography.h3>
                <x-typography.p variant="small">Jamuan lengkap untuk acara spesial Anda.</x-typography.p>
            </div>
            <div class="bg-surface rounded-xl border border-primary/10 p-6 flex flex-col text-center">
                <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-6 h-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <x-typography.h3 class="text-primary mb-2">Nasi Box</x-typography.h3>
                <x-typography.p variant="small">Praktis dan lezat untuk rapat atau acara kantor.</x-typography.p>
            </div>
        </div>
    </x-landing.section>

    {{-- 4. MENU DINE-IN --}}
    <x-landing.section id="menu-dinein" title="Menu" subtitle="Beragam hidangan Sunda siap memanjakan selera Anda.">
        {{-- Filter Tabs --}}
        <div class="flex flex-wrap gap-2 justify-center mb-8" id="menuTabs">
            <button class="menu-tab active px-3 py-1.5 rounded-full text-xs font-bold bg-primary text-white"
                onclick="filterMenu('all', this)">Semua</button>
            @foreach ($kategoris as $kat)
                <button
                    class="menu-tab px-3 py-1.5 rounded-full text-xs font-bold bg-primary/5 text-primary hover:bg-primary/10"
                    onclick="filterMenu('kat-{{ $kat->id }}', this)">{{ $kat->nama }}</button>
            @endforeach
        </div>

        {{-- Menu Grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="menuGrid">
            @foreach ($kategoris as $kategori)
                @foreach ($kategori->menus as $menu)
                    <x-landing.menu-card :menu="$menu" :kategoriNama="$kategori->nama" :filterGroup="'kat-' . $kategori->id" />
                @endforeach
            @endforeach
        </div>

        <div id="pagination-controls" class="flex justify-center gap-2 mt-8"></div>
    </x-landing.section>

    {{-- 5. PAKET CATERING --}}
    <x-landing.section id="catering" title="Paket Catering" subtitle="Pemesanan min. H-14. DP 50%." bgBatik="true">
        <div class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
            @forelse($paketCatering as $paket)
                <x-landing.package-card :paket="$paket" type="catering" />
            @empty
                <div class="col-span-full text-center py-8 text-body text-sm">Belum ada paket catering.</div>
            @endforelse
        </div>
    </x-landing.section>

    {{-- 6. PAKET NASI BOX --}}
    <x-landing.section id="nasi-box" title="Paket Nasi Box" subtitle="Pemesanan min. H-2. DP 25%." bgBatik="true">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
            @forelse($paketNasiBox as $paket)
                <x-landing.package-card :paket="$paket" type="nasi_box" />
            @empty
                <div class="col-span-full text-center py-8 text-body text-sm">Belum ada paket nasi box.</div>
            @endforelse
        </div>
    </x-landing.section>

    {{-- 8. GALERI --}}
    <x-landing.gallery />

    {{-- 9. KONTAK --}}
    <x-landing.contact />

    @push('scripts')
        <script>
            const itemsPerPage = 12;
            let currentPage = 1;
            let currentCategory = 'all';

            function renderPagination() {
                const items = Array.from(document.querySelectorAll('.menu-item'));
                const filteredItems = items.filter(item => currentCategory === 'all' || item.classList.contains(
                    currentCategory));
                const totalPages = Math.ceil(filteredItems.length / itemsPerPage);

                const controls = document.getElementById('pagination-controls');
                controls.innerHTML = '';
                if (totalPages <= 1) return;

                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.className =
                        `w-8 h-8 rounded flex items-center justify-center text-sm font-bold transition-colors ${i === currentPage ? 'bg-primary text-white' : 'bg-primary/5 text-primary hover:bg-primary/10'}`;
                    btn.innerText = i;
                    btn.onclick = () => {
                        currentPage = i;
                        updateDisplay();
                    };
                    controls.appendChild(btn);
                }
            }

            function updateDisplay() {
                const items = Array.from(document.querySelectorAll('.menu-item'));
                const filteredItems = items.filter(item => currentCategory === 'all' || item.classList.contains(
                    currentCategory));
                items.forEach(item => item.style.display = 'none');
                const startIndex = (currentPage - 1) * itemsPerPage;
                filteredItems.slice(startIndex, startIndex + itemsPerPage).forEach(item => item.style.display = '');
                renderPagination();
            }

            function filterMenu(category, btn) {
                document.querySelectorAll('.menu-tab').forEach(t => {
                    t.classList.remove('active', 'bg-primary', 'text-white');
                    t.classList.add('bg-primary/5', 'text-primary');
                });
                btn.classList.add('active', 'bg-primary', 'text-white');
                btn.classList.remove('bg-primary/5', 'text-primary');
                currentCategory = category;
                currentPage = 1;
                updateDisplay();
            }

            document.addEventListener('DOMContentLoaded', () => updateDisplay());
        </script>
    @endpush
</x-layouts.landing>
