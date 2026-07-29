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

@php $hasActiveChild = collect($items)->contains('active', true); @endphp

<div x-data="{ open: {{ $isOpen ? 'true' : 'false' }}, hover: false }" class="relative" @mouseenter="hover = true" @mouseleave="hover = false">

    {{-- Main Trigger Button --}}
    <button @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }" 
            class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-[13px] transition-all duration-200 focus:outline-none group relative overflow-hidden"
            :class="open ? 'text-white bg-gray-800/60 font-medium' : 'text-gray-500 hover:text-white hover:bg-gray-800/50 font-medium'"
            x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
        @if($hasActiveChild)
        <span class="absolute left-0 top-1.5 bottom-1.5 w-[3px] bg-white rounded-r-full"></span>
        @endif
        <div class="flex items-center gap-3">
            <i class="fa-solid {{ $icon }} w-4 text-center text-[13px] shrink-0 transition-colors duration-200"
               :class="open ? 'text-white' : 'text-gray-500 group-hover:text-white'"></i>
            <span x-show="sidebarOpen" class="whitespace-nowrap leading-none" :class="open ? 'font-semibold' : ''">{{ $label }}</span>
        </div>
        <i x-show="sidebarOpen" 
           class="fa-solid fa-chevron-down text-[9px] shrink-0 transition-transform duration-200"
           :class="open ? 'rotate-180 text-gray-300' : 'text-gray-600 group-hover:text-gray-400'"></i>
    </button>

    {{-- Dropdown (expanded sidebar) --}}
    <div x-show="open && sidebarOpen" x-collapse class="mt-0.5 py-0.5 space-y-0.5" style="display: none;">
        @foreach($items as $item)
            <a href="{{ $item['url'] }}" 
               class="flex items-center gap-2.5 ml-3 pl-5 pr-3 py-2 rounded-lg text-[12px] border-l transition-all duration-150
                      {{ $item['active'] 
                          ? 'border-white/30 text-white font-semibold bg-gray-800/50' 
                          : 'border-gray-800 text-gray-500 hover:text-white hover:bg-gray-800/30 hover:border-gray-700' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Floating Tooltip (collapsed sidebar hover) --}}
    <div x-show="hover && !sidebarOpen" x-cloak
         class="absolute left-full top-0 ml-2 w-48 bg-[#1a2332] border border-gray-700/60 rounded-xl shadow-2xl py-2 z-50 overflow-hidden"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-x-1"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-1">
        <div class="px-3 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-700/60 flex items-center gap-2">
            <i class="fa-solid {{ $icon }} text-[10px] text-gray-500"></i>
            <span>{{ $label }}</span>
        </div>
        <div class="px-1.5 pt-1.5 space-y-0.5">
            @foreach($items as $item)
                <a href="{{ $item['url'] }}" 
                   class="block px-2.5 py-1.5 text-[12px] rounded-lg transition-colors duration-150
                          {{ $item['active'] 
                              ? 'bg-gray-700/60 text-white font-semibold' 
                              : 'text-gray-400 hover:text-white hover:bg-gray-700/40' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>

</div>
