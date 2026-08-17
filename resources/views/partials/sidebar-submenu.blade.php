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
|--}}

@php $hasActiveChild = collect($items)->contains('active', true); @endphp

<div x-data="{ open: {{ $isOpen ? 'true' : 'false' }}, hover: false }" class="relative" @mouseenter="hover = true" @mouseleave="hover = false">

    {{-- Main Trigger Button --}}
    <button @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }"
            class="flex items-center rounded-xl text-sm transition-all duration-300 focus:outline-none group relative"
            :class="[
                open ? 'bg-blue-50 text-blue-700 font-semibold shadow-sm' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100/80',
                sidebarOpen ? 'w-full px-3.5 py-2.5 justify-between' : 'w-10 h-10 px-0 py-0 justify-center mx-auto'
            ]"
            x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
        <div class="flex items-center overflow-hidden" :class="sidebarOpen ? 'gap-3.5 flex-1' : ''">
            <x-dynamic-component :component="$icon"
               class="w-5 h-5 text-center shrink-0 transition-all duration-300"
               x-bind:class="open ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'" />
            <span x-show="sidebarOpen" class="whitespace-nowrap leading-none truncate text-left tracking-wide" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">{{ $label }}</span>
        </div>
        <x-heroicon-s-chevron-down x-show="sidebarOpen"
           class="w-4 h-4 shrink-0 transition-transform duration-300"
           x-bind:class="open ? 'rotate-180 text-blue-500' : 'text-slate-400 group-hover:text-slate-500'" />
    </button>

    {{-- Dropdown (expanded sidebar) --}}
    <div x-show="open && sidebarOpen" x-collapse class="mt-1 ml-[1.65rem] border-l-2 border-slate-100 py-1" style="display: none;">
        @foreach($items as $item)
            <a href="{{ $item['url'] }}"
               class="flex items-center ml-2 px-3 py-2 text-[13px] rounded-xl transition-all duration-200 group/child relative
                      {{ $item['active']
                          ? 'text-blue-700 bg-blue-50/80 font-semibold shadow-sm'
                          : 'text-slate-500 font-medium hover:text-slate-900 hover:bg-slate-50' }}">
                
                @if($item['active'])
                    <span class="absolute -left-[10px] top-0 bottom-0 w-[2px] bg-blue-600 rounded-full"></span>
                @else
                    <span class="absolute -left-[10px] top-0 bottom-0 w-[2px] bg-transparent group-hover/child:bg-slate-300 rounded-full transition-colors"></span>
                @endif
                
                <span class="tracking-wide truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Floating Tooltip (collapsed sidebar hover) --}}
    <div x-show="hover && !sidebarOpen" x-cloak
         class="absolute left-full top-0 ml-2 w-52 bg-white border border-gray-100 rounded-xl py-1.5 z-50 shadow-xl shadow-gray-200/50"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-x-1"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-1">
        <div class="px-3 py-2 text-[11px] font-bold text-[#3B82F6] uppercase tracking-wider border-b border-gray-50 flex items-center gap-2">
            <span>{{ $label }}</span>
        </div>
        <div class="px-1.5 pt-1.5 pb-1 space-y-0.5">
            @foreach($items as $item)
                <a href="{{ $item['url'] }}"
                   class="block px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                          {{ $item['active']
                              ? 'bg-[#3B82F6]/10 text-[#3B82F6]'
                              : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>

</div>
