@props(['paket', 'type'])

<div class="bg-surface rounded-xl border border-primary/10 p-6 flex flex-col shadow-sm">
    <x-typography.h3 class="text-primary mb-2">{{ $paket->nama_paket ?? $paket->nama }}</x-typography.h3>
    <div class="text-secondary font-bold mb-1">
        @if($paket->harga > 0)
            Rp {{ number_format($paket->harga, 0, ',', '.') }}/{{ $type === 'catering' ? 'porsi' : 'box' }}
        @else
            By request
        @endif
    </div>
    @if($type === 'nasi_box')
        <p class="text-[11px] text-body mb-3">Min. 10 box</p>
    @else
        <div class="mb-3"></div>
    @endif
    @if($paket->deskripsi)
        <x-typography.p variant="small" class="leading-relaxed mb-4 flex-1">{{ $paket->deskripsi }}</x-typography.p>
    @endif
    @if($type === 'catering' && $paket->komponens && $paket->komponens->count() > 0)
        <ul class="space-y-1.5 mb-4 text-sm text-body">
            @foreach($paket->komponens as $komp)
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-secondary mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $komp->nama_komponen }}
                </li>
            @endforeach
        </ul>
    @endif
    <a href="{{ $type === 'catering' ? route('pesan.catering') : route('pesan.nasibox') }}" class="mt-auto bg-primary text-white px-5 py-2 rounded-md font-bold text-sm hover:bg-primary-container transition-all shadow-sm text-center">
        Pesan {{ $type === 'catering' ? 'Catering' : 'Nasi Box' }}
    </a>
</div>
