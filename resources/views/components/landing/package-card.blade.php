@props(['paket', 'type'])

<div class="bg-white border border-neutral-200/80 rounded-xl overflow-hidden flex flex-col transition-all duration-300 hover:border-neutral-300 hover:shadow-xs">
    {{-- Foto --}}
    <div class="relative h-32 sm:h-36 w-full bg-neutral-50 overflow-hidden shrink-0">
        @php
            $defaultImg = asset('images/homepage.webp');
            if ($type === 'nasi_box') {
                $defaultImg = asset('images/nasibox.webp');
            } else if ($type === 'catering') {
                $defaultImg = asset('images/catering.webp');
            }
            $imgSrc = $paket->foto ? Storage::url($paket->foto) : $defaultImg;
        @endphp
        <img src="{{ $imgSrc }}" alt="{{ $paket->nama_paket ?? $paket->nama }}" class="w-full h-full object-cover">
    </div>

    {{-- Body --}}
    <div class="p-3.5 flex-1 flex flex-col">
        <h3 class="text-sm font-bold text-neutral-900 tracking-tight mb-1">{{ $paket->nama_menu ?? $paket->nama_paket ?? $paket->nama }}</h3>

        <div class="mb-2">
            @if(($paket->harga_jual ?? $paket->harga) > 0)
                <span class="text-neutral-900 font-bold text-xs">Rp {{ number_format($paket->harga_jual ?? $paket->harga, 0, ',', '.') }}</span>
                <span class="text-[10px] text-neutral-400">/{{ $type === 'catering' ? 'porsi' : 'box' }}</span>
            @else
                <span class="text-xs font-semibold text-neutral-500">By request</span>
            @endif
        </div>

        @if($paket->deskripsi)
            <p class="text-xs text-neutral-600 leading-relaxed line-clamp-2 mb-2.5">{{ $paket->deskripsi }}</p>
        @endif

        @if($paket->komponen_paket && $paket->komponen_paket->count() > 0)
            <ul class="space-y-1 mb-3 text-xs text-neutral-600">
                @foreach($paket->komponen_paket as $komp)
                    <li class="flex items-start gap-1.5">
                        <span class="w-1 h-1 rounded-full bg-neutral-400 mt-1.5 shrink-0"></span>
                        <span class="line-clamp-1">
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
           onclick="handleOrderClick(event, this.href)"
           class="mt-auto inline-flex items-center justify-center gap-1.5 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
            <span>Pesan Sekarang</span>
            <x-heroicon-o-arrow-right class="w-3 h-3" />
        </a>
    </div>
</div>
