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
            class="flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-sm transition-colors duration-200 focus:outline-none group relative overflow-hidden"
            :class="open ? 'text-neutral-900 bg-neutral-100 font-medium' : 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-50'"
            x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
        @if($hasActiveChild)
        <span class="absolute left-0 top-1.5 bottom-1.5 w-[2.5px] bg-neutral-900"></span>
        @endif
        <div class="flex items-center gap-3 flex-1 overflow-hidden">
            <x-dynamic-component :component="$icon"
               class="w-5 h-5 text-center shrink-0 transition-colors duration-200"
               x-bind:class="open ? 'text-neutral-900' : 'text-neutral-400 group-hover:text-neutral-900'" />
            <span x-show="sidebarOpen" class="whitespace-nowrap leading-none truncate text-left">{{ $label }}</span>
        </div>
        <x-heroicon-s-chevron-down x-show="sidebarOpen"
           class="w-3 h-3 shrink-0 transition-transform duration-200"
           x-bind:class="open ? 'rotate-180 text-neutral-500' : 'text-neutral-400 group-hover:text-neutral-500'" />
    </button>

    {{-- Dropdown (expanded sidebar) --}}
    <div x-show="open && sidebarOpen" x-collapse class="mt-0.5 space-y-0.5" style="display: none;">
        @foreach($items as $item)
            <a href="{{ $item['url'] }}"
               class="flex items-center gap-2.5 ml-4 pl-4 pr-3 py-2 rounded-xl text-[13.5px] border-l transition-colors duration-150
                      {{ $item['active']
                          ? 'border-neutral-900 text-neutral-900 font-medium bg-neutral-50'
                          : 'border-neutral-200 text-neutral-500 hover:text-neutral-900 hover:bg-neutral-50 hover:border-neutral-400' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Floating Tooltip (collapsed sidebar hover) --}}
    <div x-show="hover && !sidebarOpen" x-cloak
         class="absolute left-full top-0 ml-2 w-52 bg-white border border-neutral-200 rounded-xl py-1.5 z-50"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-x-1"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-1">
        <div class="px-3 py-2 text-[11px] font-semibold text-neutral-500 uppercase tracking-widest border-b border-neutral-100 flex items-center gap-2">
            <span>{{ $label }}</span>
        </div>
        <div class="px-1.5 pt-1.5 pb-1 space-y-0.5">
            @foreach($items as $item)
                <a href="{{ $item['url'] }}"
                   class="block px-3 py-2 rounded-xl text-sm transition-colors duration-150
                          {{ $item['active']
                              ? 'bg-neutral-100 text-neutral-900 font-medium'
                              : 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-50' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>

</div>
