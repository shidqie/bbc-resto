{{--
|--------------------------------------------------------------------------
| Sidebar Submenu (Dropdown)
|--------------------------------------------------------------------------
| Partial untuk menampilkan submenu navigasi di sidebar.
| Menggunakan Alpine.js (x-data, x-show, x-collapse).
|
| Variabel:
|   - $icon   (string) : Heroicon name (contoh: "cube", "sparkles")
|   - $label  (string) : Teks grup menu (contoh: "Bahan Baku")
|   - $isOpen (bool)   : Apakah submenu terbuka secara default
|   - $items  (array)  : Array of array(label, url, active)
--}}

@php $hasActiveChild = collect($items)->contains('active', true); @endphp

<div x-data="{ open: {{ $isOpen ? 'true' : 'false' }}, hover: false }" class="relative" @mouseenter="hover = true" @mouseleave="hover = false">

    {{-- Main Trigger Button --}}
    <button @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }" 
            class="flex items-center justify-between w-full px-3 py-3 rounded-lg text-[15px] transition-all duration-200 focus:outline-none group relative overflow-hidden"
            :class="open ? 'text-white bg-white/5 font-semibold' : 'text-gray-300 hover:text-white hover:bg-white/5 font-medium'"
            x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
        @if($hasActiveChild)
        <span class="absolute left-0 top-1.5 bottom-1.5 w-[3px] bg-white rounded-r-full"></span>
        @endif
        <div class="flex items-center gap-3 flex-1 overflow-hidden">
            <x-dynamic-component :component="$icon" 
               class="w-6 h-6 text-center shrink-0 transition-colors duration-200"
               x-bind:class="open ? 'text-white' : 'text-gray-400 group-hover:text-white'" />
            <span x-show="sidebarOpen" class="whitespace-nowrap leading-none truncate text-left" :class="open ? 'font-semibold' : ''">{{ $label }}</span>
        </div>
        <x-heroicon-s-chevron-down x-show="sidebarOpen" 
           class="w-3 h-3 shrink-0 transition-transform duration-200"
           x-bind:class="open ? 'rotate-180 text-gray-300' : 'text-gray-600 group-hover:text-gray-400'" />
    </button>

    {{-- Dropdown (expanded sidebar) --}}
    <div x-show="open && sidebarOpen" x-collapse class="mt-0.5 py-0.5 space-y-0.5" style="display: none;">
        @foreach($items as $item)
            <a href="{{ $item['url'] }}" 
               class="flex items-center gap-2.5 ml-3 pl-5 pr-3 py-2.5 rounded-lg text-[14px] border-l transition-all duration-150
                      {{ $item['active'] 
                          ? 'border-white/50 text-white font-semibold bg-white/10 shadow-sm' 
                          : 'border-white/10 text-gray-300 hover:text-white hover:bg-white/5 hover:border-white/30' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Floating Tooltip (collapsed sidebar hover) --}}
    <div x-show="hover && !sidebarOpen" x-cloak
         class="absolute left-full top-0 ml-2 w-52 bg-[#1a2332] border border-gray-700/60 rounded-xl shadow-2xl py-2 z-50 overflow-hidden"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-x-1"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-1">
        <div class="px-3 py-2.5 text-[12px] font-bold text-gray-300 uppercase tracking-widest border-b border-gray-700/60 flex items-center gap-2">
            <x-heroicon-o-sparkles class="{{ $icon }} w-3 h-3 text-gray-400" />
            <span>{{ $label }}</span>
        </div>
        <div class="px-2 pt-2 pb-1.5 space-y-1">
            @foreach($items as $item)
                <a href="{{ $item['url'] }}" 
                   class="block px-3 py-2.5 text-[14px] rounded-lg transition-colors duration-150
                          {{ $item['active'] 
                              ? 'bg-white/10 text-white font-semibold shadow-sm' 
                              : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>

</div>
