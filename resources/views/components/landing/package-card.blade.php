@props(['paket', 'type'])

<div class="bg-white border border-neutral-200 rounded-lg overflow-hidden flex flex-col transition-colors duration-300 hover:border-neutral-300">
    {{-- Foto --}}
    <div class="relative h-56 md:h-64 w-full bg-neutral-50 overflow-hidden shrink-0">
        @if($paket->foto)
            <img src="{{ Storage::url($paket->foto) }}" alt="{{ $paket->nama_paket ?? $paket->nama }}" class="w-full h-full object-cover">
        @else
            @php
                $defaultImg = asset('images/homepage.webp');
                if ($type === 'nasi_box') {
                    $defaultImg = asset('images/saungbabakan.webp');
                } else if ($type === 'catering') {
                    $defaultImg = asset('images/taman_kafe.webp');
                }
            @endphp
            <img src="{{ $defaultImg }}" alt="{{ $paket->nama_paket ?? $paket->nama }}" class="w-full h-full object-cover">
        @endif
    </div>

    {{-- Body --}}
    <div class="p-5 flex-1 flex flex-col">
        <h3 class="text-xl font-semibold text-neutral-900 tracking-tight mb-1">{{ $paket->nama_menu ?? $paket->nama_paket ?? $paket->nama }}</h3>

        <div class="mb-3">
            @if(($paket->harga_jual ?? $paket->harga) > 0)
                <span class="text-neutral-900 font-semibold">Rp {{ number_format($paket->harga_jual ?? $paket->harga, 0, ',', '.') }}</span>
                <span class="text-xs text-neutral-400">/{{ $type === 'catering' ? 'porsi' : 'box' }}</span>
            @else
                <span class="text-sm text-neutral-500">By request</span>
            @endif
        </div>

        @if($paket->deskripsi)
            <p class="text-sm text-neutral-600 leading-relaxed mb-4">{{ $paket->deskripsi }}</p>
        @endif

        @if($paket->komponen_paket && $paket->komponen_paket->count() > 0)
            <ul class="space-y-2 mb-6 text-sm text-neutral-600">
                @foreach($paket->komponen_paket as $komp)
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 mt-1.5 shrink-0"></span>
                        <span>
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
           class="mt-auto inline-flex items-center justify-center gap-2 bg-neutral-900 hover:bg-neutral-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            <span>Pesan {{ $type === 'catering' ? 'Katering' : 'Nasi Box' }}</span>
            <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
        </a>
    </div>
</div>
