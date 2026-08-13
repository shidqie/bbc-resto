@props(['quantity' => null, 'title', 'subtitle' => null, 'value' => null])

<div class="flex items-start justify-between p-4 bg-white hover:bg-gray-50/50 hover:rounded-xl transition-all">
    <div class="flex items-start gap-3">
        @if($quantity)
            <span class="text-sm font-semibold text-gray-900 mt-0.5 min-w-[24px]">{{ $quantity }}</span>
        @endif
        
        <div>
            <p class="text-sm font-medium text-gray-900">{{ $title }}</p>
            @if($subtitle)
                <p class="text-[13px] text-gray-500 mt-0.5">{{ $subtitle }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
    
    @if($value)
        <div class="text-sm font-semibold text-gray-900 text-right ml-4 shrink-0">
            {{ $value }}
        </div>
    @endif
</div>
