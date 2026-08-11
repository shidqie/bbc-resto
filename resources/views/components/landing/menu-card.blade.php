@props(['menu', 'kategoriNama', 'filterGroup' => 'lainnya'])

<div class="menu-item {{ $filterGroup }} bg-white border border-neutral-200/80 rounded-xl overflow-hidden flex flex-col transition-all duration-300 hover:border-neutral-300 hover:shadow-xs">
    <div class="h-28 sm:h-32 w-full bg-neutral-50 flex items-center justify-center relative">
        @if($menu->foto)
            <img src="{{ Storage::url($menu->foto) }}" alt="{{ $menu->nama }}" class="w-full h-full object-cover">
        @else
            <svg class="w-8 h-8 text-neutral-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        @endif
        <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-xs border border-neutral-200 px-1.5 py-0.5 rounded text-[10px] font-semibold text-neutral-500 uppercase tracking-wider">
            {{ $kategoriNama }}
        </div>
    </div>
    <div class="p-3 flex-1 flex flex-col">
        <h3 class="text-sm font-bold text-neutral-900 tracking-tight mb-1 line-clamp-1" title="{{ $menu->nama_menu ?? $menu->nama }}">{{ $menu->nama_menu ?? $menu->nama }}</h3>
        @if($menu->deskripsi)
            <p class="text-xs text-neutral-500 leading-relaxed line-clamp-1 mb-2">{{ $menu->deskripsi }}</p>
        @endif
        <div class="mt-auto pt-2 border-t border-neutral-100 flex justify-between items-center">
            <span class="text-neutral-900 font-bold text-xs">Rp {{ number_format($menu->harga_jual ?? $menu->harga, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
