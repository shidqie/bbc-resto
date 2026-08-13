@props(['date', 'time', 'title', 'subtitle' => null, 'icon' => 'check-circle'])

<div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
    {{-- Icon --}}
    <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-blue-50 text-blue-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
        <x-dynamic-component :component="'heroicon-s-' . $icon" class="w-5 h-5" />
    </div>
    
    {{-- Content --}}
    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between mb-1">
            <h4 class="font-semibold text-gray-900 text-sm">{{ $title }}</h4>
            <span class="text-xs font-medium text-gray-500">{{ $time }}</span>
        </div>
        @if($subtitle)
            <p class="text-[13px] text-gray-500 mb-2">{{ $subtitle }}</p>
        @endif
        <div class="text-[11px] font-medium text-gray-400 bg-gray-50 inline-block px-2 py-1 rounded-md">
            {{ $date }}
        </div>
    </div>
</div>
