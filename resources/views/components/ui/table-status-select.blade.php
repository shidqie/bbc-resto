@props([
    'id' => null,
    'name' => 'status',
    'current' => 1,
    'allowed' => [],
    'isDineIn' => false,
])

@php
    $uniqueId = $id ?? uniqid();

    $defaultLabels = [
        1 => 'Menunggu Konfirmasi',
        2 => 'Dikonfirmasi',
        3 => 'Sedang Diproses',
        4 => 'Pesanan Siap',
        8 => 'Pesanan Telah Dihidangkan',
        5 => 'Selesai',
        6 => 'Dibatalkan',
    ];

    $statusStyles = [
        1 => [
            'badge' => 'text-amber-800 bg-amber-50 border-amber-200/90 hover:bg-amber-100/80',
            'dot' => 'bg-amber-500',
        ],
        2 => [
            'badge' => 'text-blue-800 bg-blue-50 border-blue-200/90 hover:bg-blue-100/80',
            'dot' => 'bg-blue-500',
        ],
        3 => [
            'badge' => 'text-indigo-800 bg-indigo-50 border-indigo-200/90 hover:bg-indigo-100/80',
            'dot' => 'bg-indigo-500',
        ],
        4 => [
            'badge' => 'text-purple-800 bg-purple-50 border-purple-200/90 hover:bg-purple-100/80',
            'dot' => 'bg-purple-500',
        ],
        8 => [
            'badge' => 'text-teal-800 bg-teal-50 border-teal-200/90 hover:bg-teal-100/80',
            'dot' => 'bg-teal-500',
        ],
        5 => [
            'badge' => 'text-emerald-800 bg-emerald-50 border-emerald-200/90 hover:bg-emerald-100/80',
            'dot' => 'bg-emerald-500',
        ],
        6 => [
            'badge' => 'text-rose-800 bg-rose-50 border-rose-200/90 hover:bg-rose-100/80',
            'dot' => 'bg-rose-500',
        ],
    ];

    $currentStyle = $statusStyles[$current] ?? [
        'badge' => 'text-gray-700 bg-gray-50 border-gray-200 hover:bg-gray-100',
        'dot' => 'bg-gray-400',
    ];

    $currentLabel = $defaultLabels[$current] ?? 'Status #' . $current;

    $optionsToRender = !empty($allowed) ? $allowed : array_keys($defaultLabels);
@endphp

<div x-data="{ 
        open: false,
        topPos: 0,
        leftPos: 0,
        toggle(btn) {
            if (this.open) {
                this.open = false;
                return;
            }
            const rect = btn.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const menuHeight = 220;
            
            if (spaceBelow < menuHeight && rect.top > menuHeight) {
                this.topPos = rect.top - menuHeight - 4;
            } else {
                this.topPos = rect.bottom + 4;
            }
            
            this.leftPos = Math.max(10, Math.min(window.innerWidth - 210, rect.left));
            this.open = true;
        }
     }" 
     @click.outside="open = false" 
     @scroll.window="open = false"
     class="relative inline-block text-left">
    
    {{-- Hidden Form Input --}}
    <input type="hidden" name="{{ $name }}" id="status-input-{{ $uniqueId }}" value="{{ $current }}">

    {{-- Trigger Button --}}
    <button type="button" 
            @click="toggle($el)" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold shadow-2xs transition-all cursor-pointer select-none {{ $currentStyle['badge'] }}"
            aria-haspopup="true"
            :aria-expanded="open">
        <span class="whitespace-nowrap">{{ $currentLabel }}</span>
        <x-heroicon-o-chevron-down class="w-3.5 h-3.5 opacity-60 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
    </button>

    {{-- Custom Styled Floating Menu (Menggunakan Fixed Positioning agar tidak terpotong oleh overflow-x / table border) --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         :style="`top: ${topPos}px; left: ${leftPos}px;`"
         class="fixed z-[99999] min-w-[190px] bg-white border border-gray-200 shadow-2xl rounded-2xl py-1.5 text-xs overflow-hidden ring-1 ring-black/10"
         style="display: none;">
        
        <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 mb-1">
            Ubah Status
        </div>

        @foreach($optionsToRender as $optVal)
            @php
                $optLabel = $defaultLabels[$optVal] ?? 'Status #' . $optVal;
                $isSelected = $current == $optVal;
            @endphp
            <button type="button" 
                    @click="
                        if ('{{ $current }}' != '{{ $optVal }}') {
                            document.getElementById('status-input-{{ $uniqueId }}').value = '{{ $optVal }}';
                            $el.closest('form').submit();
                        }
                        open = false;
                    "
                    class="w-full px-3.5 py-2 text-left flex items-center justify-between transition-colors cursor-pointer {{ $isSelected ? 'bg-emerald-50/90 text-emerald-900 font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium' }}">
                <span class="whitespace-nowrap">{{ $optLabel }}</span>
                @if($isSelected)
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                @endif
            </button>
        @endforeach
    </div>
</div>
