@props(['paket', 'type'])

<div class="group flex flex-col bg-white dark:bg-surface rounded-2xl overflow-hidden shadow-[0_1px_3px_rgba(0,0,0,0.06)]">
    {{-- Foto --}}
    <div class="relative h-52 sm:h-56 w-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden shrink-0">
        @php
            $defaultImg = asset('images/homepage.webp');
            if ($type === 'nasi_box') {
                $defaultImg = asset('images/nasibox.webp');
            } else if ($type === 'catering') {
                $defaultImg = asset('images/catering.webp');
            }
            $imgSrc = $paket->foto ? Storage::url($paket->foto) : $defaultImg;
        @endphp
        <img src="{{ $imgSrc }}" alt="{{ $paket->nama_paket ?? $paket->nama }}" class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-90">
    </div>

    {{-- Body --}}
    <div class="p-5 flex-1 flex flex-col">
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 tracking-tight mb-1">{{ $paket->nama_menu ?? $paket->nama_paket ?? $paket->nama }}</h3>

        <div class="mb-3">
            @if(($paket->harga_jual ?? $paket->harga) > 0)
                <span class="text-neutral-900 dark:text-neutral-100 font-semibold text-sm">Rp {{ number_format($paket->harga_jual ?? $paket->harga, 0, ',', '.') }}</span>
                <span class="text-xs text-neutral-400 dark:text-neutral-400">/{{ $type === 'catering' ? 'porsi' : 'box' }}</span>
            @else
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">By request</span>
            @endif
        </div>

        @if($paket->deskripsi)
            <p class="text-sm text-neutral-500 dark:text-neutral-300 leading-relaxed line-clamp-2 mb-4">{{ $paket->deskripsi }}</p>
        @endif

        @if($paket->komponen_paket && $paket->komponen_paket->count() > 0)
            <ul class="mb-5 divide-y divide-neutral-100 dark:divide-neutral-700">
                @foreach($paket->komponen_paket as $komp)
                    <li class="flex items-start justify-between gap-3 py-1.5 text-sm">
                        <span class="line-clamp-1 text-neutral-500 dark:text-neutral-400">
                            @if($type === 'nasi_box' && $komp->opsi && $komp->opsi->count() > 0)
                                {{ $komp->opsi->pluck('nama_pilihan')->join(', ') }}
                            @else
                                {{ $komp->nama_komponen }}
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ $type === 'catering' ? route('pesan.catering', ['paket_id' => $paket->id]) : route('pesan.nasibox', ['paket_id' => $paket->id]) }}"
           class="mt-auto inline-flex items-center justify-center gap-1.5 bg-neutral-900 dark:bg-primary dark:text-neutral-900 hover:bg-neutral-800 dark:hover:bg-primary-container dark:hover:text-white text-white text-xs font-medium px-4 py-2.5 rounded-lg transition-colors duration-300 self-start">
            <span>Pesan Sekarang</span>
            <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
        </a>
    </div>
</div>