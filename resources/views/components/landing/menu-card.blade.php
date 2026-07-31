@props(['menu', 'kategoriNama', 'filterGroup' => 'lainnya'])

<div class="menu-item {{ $filterGroup }} bg-surface border border-primary/10 rounded-xl overflow-hidden flex flex-col shadow-sm">
    <div class="h-40 w-full bg-primary/5 flex items-center justify-center relative">
        @if($menu->foto)
            <img src="{{ Storage::url($menu->foto) }}" alt="{{ $menu->nama }}" class="w-full h-full object-cover">
        @else
            <svg class="w-12 h-12 text-primary/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        @endif
        <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded text-[10px] font-bold text-primary uppercase tracking-wider">
            {{ $kategoriNama }}
        </div>
    </div>
    <div class="p-4 flex-1 flex flex-col">
        <x-typography.h3 class="!text-base mb-2 line-clamp-2 !text-primary">{{ $menu->nama_menu ?? $menu->nama }}</x-typography.h3>
        @if($menu->deskripsi)
            <x-typography.p variant="small" class="line-clamp-2 mb-2">{{ $menu->deskripsi }}</x-typography.p>
        @endif
        <div class="mt-auto pt-3 border-t border-primary/5 flex justify-between items-center">
            <span class="text-secondary font-bold text-base">Rp {{ number_format($menu->harga_jual ?? $menu->harga, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
