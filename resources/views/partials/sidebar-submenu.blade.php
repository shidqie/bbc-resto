{{--
|--------------------------------------------------------------------------
| Sidebar Submenu (Dropdown)
|--------------------------------------------------------------------------
| Partial untuk menampilkan submenu navigasi di sidebar.
| Menggunakan Alpine.js (x-data, x-show, x-collapse).
|
| Variabel:
|   - $icon   (string) : Nama Heroicon (contoh: "o-cube")
|   - $label  (string) : Teks grup menu (contoh: "Bahan Baku")
|   - $isOpen (bool)   : Apakah submenu terbuka secara default
|   - $items  (array)  : Array of array(label, url, active)
--}}

<div x-data="{ open: {{ $isOpen ? 'true' : 'false' }}, hover: false }" class="pt-1 relative" @mouseenter="hover = true" @mouseleave="hover = false">
    {{-- Main Button --}}
    <button @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }" 
            class="flex items-center justify-between w-full px-3 py-3 rounded-xl text-sm transition focus:outline-none group hover:text-white hover:bg-gray-800/50" 
            x-bind:class="open || (hover && !sidebarOpen) ? 'text-white bg-gray-800/50' : ''">
        <div class="flex items-center gap-3">
            @svg('heroicon-' . $icon, 'w-6 h-6 shrink-0', ['x-bind:class' => "open || (hover && !sidebarOpen) ? 'text-white' : 'text-gray-400 group-hover:text-white'"])
            <span x-show="sidebarOpen" class="whitespace-nowrap transition-opacity duration-200" x-bind:class="open ? 'font-medium' : ''">{{ $label }}</span>
        </div>
        <x-heroicon-o-chevron-down x-show="sidebarOpen" class="w-4 h-4 transition-transform duration-200" x-bind:class="open ? 'rotate-180 text-white' : 'text-gray-500 group-hover:text-white'" />
    </button>
    
    {{-- Dropdown (saat sidebar terbuka) --}}
    <div x-show="open && sidebarOpen" 
         x-collapse
         class="pl-12 pr-2 py-1 space-y-1 mt-1" style="display: none;">
        @foreach($items as $item)
            <a href="{{ $item['url'] }}" 
               class="block text-sm text-gray-400 hover:text-white py-2 rounded-lg transition whitespace-nowrap {{ $item['active'] ? 'text-white font-medium' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
    
    {{-- Floating Menu (saat sidebar tertutup & di-hover) --}}
    <div x-show="hover && !sidebarOpen" style="display: none;" 
         class="absolute left-full top-0 ml-4 w-52 bg-gray-900/95 backdrop-blur-md border border-gray-700/50 rounded-2xl shadow-2xl py-2 z-50" 
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" 
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-2">
        <div class="px-4 py-2 text-[11px] font-bold text-blue-400/90 uppercase tracking-widest mb-1 border-b border-gray-700/50">{{ $label }}</div>
        <div class="px-2 space-y-0.5 mt-2">
            @foreach($items as $item)
                <a href="{{ $item['url'] }}" 
                   class="block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800/80 rounded-lg transition-colors {{ $item['active'] ? 'bg-blue-600/10 !text-blue-400 font-medium' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
