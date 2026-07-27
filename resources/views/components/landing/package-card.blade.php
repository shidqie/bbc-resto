@props(['paket', 'type'])

<div class="bg-surface rounded-2xl border border-primary/10 overflow-hidden flex flex-col shadow-sm hover:shadow-md transition-all duration-300 group">
    {{-- Card Header Image / Foto --}}
    <div class="relative h-44 w-full bg-primary/5 overflow-hidden">
        @if($paket->foto)
            <img src="{{ Storage::url($paket->foto) }}" alt="{{ $paket->nama_paket ?? $paket->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            @php
                // Default high quality image based on package name/type
                $defaultImg = asset('images/homepage.webp');
                if ($type === 'nasi_box') {
                    $defaultImg = asset('images/saungbabakan.webp');
                } else if ($type === 'catering') {
                    $defaultImg = asset('images/taman_kafe.webp');
                }
            @endphp
            <img src="{{ $defaultImg }}" alt="{{ $paket->nama_paket ?? $paket->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @endif

        {{-- Type Badge --}}
        <span class="absolute top-3 left-3 bg-surface/90 backdrop-blur-md text-primary font-extrabold text-[11px] px-3 py-1 rounded-full border border-primary/10 shadow-xs uppercase tracking-wider">
            {{ $type === 'catering' ? 'Catering' : 'Nasi Box' }}
        </span>
    </div>

    {{-- Card Body Content --}}
    <div class="p-6 flex-1 flex flex-col">
        <x-typography.h3 class="text-primary mb-1.5 font-extrabold">{{ $paket->nama_paket ?? $paket->nama }}</x-typography.h3>
        
        <div class="text-secondary font-extrabold text-base mb-1">
            @if($paket->harga > 0)
                Rp {{ number_format($paket->harga, 0, ',', '.') }}<span class="text-xs text-body/70 font-medium">/{{ $type === 'catering' ? 'porsi' : 'box' }}</span>
            @else
                <span class="text-sm">By request</span>
            @endif
        </div>

        @if($type === 'nasi_box')
            <p class="text-[11px] text-body/70 font-semibold mb-3 flex items-center gap-1">
                <i class="fa-solid fa-circle-info text-[10px] text-secondary"></i> Min. 10 box
            </p>
        @else
            <div class="mb-3"></div>
        @endif

        @if($paket->deskripsi)
            <x-typography.p variant="small" class="leading-relaxed mb-4 text-body/90 text-xs flex-1">{{ $paket->deskripsi }}</x-typography.p>
        @endif

        @if($type === 'catering' && $paket->komponens && $paket->komponens->count() > 0)
            <ul class="space-y-1.5 mb-4 text-xs text-body bg-primary/5 p-3 rounded-xl border border-primary/5">
                @foreach($paket->komponens as $komp)
                    <li class="flex items-start gap-2 font-medium">
                        <i class="fa-solid fa-check text-secondary text-[11px] mt-0.5 shrink-0"></i>
                        <span>{{ $komp->nama_komponen }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ $type === 'catering' ? route('pesan.catering') : route('pesan.nasibox') }}" 
           class="mt-auto bg-primary hover:bg-primary-container text-white px-5 py-2.5 rounded-xl font-extrabold text-xs transition-all shadow-sm text-center flex items-center justify-center gap-2 group-hover:bg-primary-container">
            <span>Pesan {{ $type === 'catering' ? 'Catering' : 'Nasi Box' }}</span>
            <i class="fa-solid fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1"></i>
        </a>
    </div>
</div>
