@props([
    'id' => 'drawer',
])

<div id="{{ $id }}"
     x-data="{ show: false }"
     x-show="show"
     @open-{{ $id }}.window="show = true"
     @close-{{ $id }}.window="show = false"
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden"
     aria-labelledby="slide-over-title" 
     role="dialog" 
     aria-modal="true"
     @keydown.escape.window="show = false">
    
    {{-- Overlay --}}
    <div x-show="show"
         x-transition:enter="ease-in-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in-out duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-black/30 backdrop-blur-sm transition-opacity"
         @click="show = false"></div>

    <div class="fixed inset-y-0 right-0 flex max-w-full">
        {{-- Drawer Panel --}}
        <div x-show="show"
             x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-screen h-screen md:w-[50vw] md:min-w-[680px] md:max-w-[820px] bg-white shadow-2xl flex flex-col md:rounded-l-2xl">
            
            {{ $slot }}

        </div>
    </div>
</div>
