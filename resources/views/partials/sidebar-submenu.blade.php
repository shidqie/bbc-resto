{{--
|--------------------------------------------------------------------------
| Sidebar Submenu (Dropdown)
|--------------------------------------------------------------------------
| Partial untuk menampilkan submenu navigasi di sidebar.
| Menggunakan Alpine.js (x-data, x-show, x-collapse).
|
| Variabel:
|   - $icon   (string) : FontAwesome icon class (contoh: "fa-cube")
|   - $label  (string) : Teks grup menu (contoh: "Bahan Baku")
|   - $isOpen (bool)   : Apakah submenu terbuka secara default
|   - $items  (array)  : Array of array(label, url, active)
--}}

<div x-data="{ open: {{ $isOpen ? 'true' : 'false' }}, hover: false }" class="pt-1 relative" @mouseenter="hover = true" @mouseleave="hover = false">
    {{-- Main Button --}}
    <button @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }" 
            class="flex items-center justify-between w-full px-3 py-3 rounded-xl text-sm transition focus:outline-none group text-gray-400 hover:text-white hover:bg-gray-800/50" 
            x-bind:class="open || (hover && !sidebarOpen) ? 'text-white bg-gray-800/50' : ''"
            x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
        <div class="flex items-center gap-3">
            <i class="fa-solid {{ $icon }} w-6 text-center text-[16px] shrink-0" x-bind:class="open || (hover && !sidebarOpen) ? 'text-emerald-400' : 'text-gray-400 group-hover:text-white'"></i>
            <span x-show="sidebarOpen" class="whitespace-nowrap transition-opacity duration-200" x-bind:class="open ? 'font-bold' : ''">{{ $label }}</span>
        </div>
        <i x-show="sidebarOpen" class="fa-solid fa-chevron-down text-[12px] transition-transform duration-200" x-bind:class="open ? 'rotate-180 text-white' : 'text-gray-500 group-hover:text-white'"></i>
    </button>
    
    {{-- Dropdown (saat sidebar terbuka) --}}
    <div x-show="open && sidebarOpen" 
         x-collapse
         class="pl-12 pr-2 py-1 space-y-1 mt-1" style="display: none;">
        @foreach($items as $item)
            <a href="{{ $item['url'] }}" 
               class="block text-sm text-gray-400 hover:text-white py-2 px-2.5 rounded-lg transition whitespace-nowrap {{ $item['active'] ? 'text-emerald-400 font-bold bg-gray-800/40' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
    
    {{-- Floating Menu (saat sidebar tertutup & di-hover) --}}
    <div x-show="hover && !sidebarOpen" style="display: none;" 
         class="absolute left-full top-0 ml-3 w-52 bg-[#111827] border border-gray-700/80 rounded-2xl shadow-2xl py-2 z-50 overflow-hidden" 
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" 
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-2">
        <div class="px-4 py-2 text-[11px] font-black text-emerald-400 uppercase tracking-wider border-b border-gray-800 flex items-center gap-2">
            <i class="fa-solid {{ $icon }} text-xs"></i>
            <span>{{ $label }}</span>
        </div>
        <div class="px-2 space-y-0.5 mt-2">
            @foreach($items as $item)
                <a href="{{ $item['url'] }}" 
                   class="block px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-xl transition-colors {{ $item['active'] ? 'bg-[#0F2E23] text-emerald-300 font-bold' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
