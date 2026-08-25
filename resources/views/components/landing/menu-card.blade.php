@props(['menu', 'kategoriNama', 'filterGroup' => 'lainnya'])

<div onclick="openMenuModal({ id: {{ $menu->id ?? $menu->id_menu }}, nama: '{{ addslashes($menu->nama_menu ?? $menu->nama) }}', deskripsi: '{{ addslashes(str_replace(array("\r", "\n"), ' ', $menu->deskripsi)) }}', harga: {{ $menu->harga_jual ?? $menu->harga }}, foto: '{{ $menu->foto_url ?? ($menu->foto ? Storage::url($menu->foto) : '') }}', kategori: '{{ addslashes($kategoriNama) }}' })" class="menu-item {{ $filterGroup }} group flex flex-col cursor-pointer bg-white dark:bg-neutral-900/40 rounded-2xl overflow-hidden border border-neutral-100 dark:border-white/5 hover:border-primary/30 dark:hover:border-primary/50 hover:shadow-lg hover:shadow-primary/5 transition-all duration-300">
    <div class="h-40 w-full bg-neutral-50 dark:bg-neutral-800/50 flex items-center justify-center overflow-hidden">
        @php
            $cardImg = $menu->foto_url ?? ($menu->foto ? (str_starts_with($menu->foto, 'images/') ? asset($menu->foto) : Storage::url($menu->foto)) : null);
        @endphp
        @if($cardImg)
            <img src="{{ $cardImg }}" alt="{{ $menu->nama ?? $menu->nama_menu }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <svg class="w-8 h-8 text-neutral-300 dark:text-neutral-600/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        @endif
    </div>
    <div class="p-4 flex-1 flex flex-col">
        <p class="text-[10px] font-bold text-primary dark:text-primary/90 uppercase tracking-widest mb-1.5">{{ $kategoriNama }}</p>
        <h3 class="text-sm sm:text-base font-semibold text-neutral-900 dark:text-neutral-100 tracking-tight leading-snug mb-1 line-clamp-1" title="{{ $menu->nama_menu ?? $menu->nama }}">{{ $menu->nama_menu ?? $menu->nama }}</h3>
        @if($menu->deskripsi)
            <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed line-clamp-2 mb-3">{{ $menu->deskripsi }}</p>
        @endif
        <span class="mt-auto text-neutral-900 dark:text-neutral-100 font-bold text-sm">Rp {{ number_format($menu->harga_jual ?? $menu->harga, 0, ',', '.') }}</span>
    </div>
</div>