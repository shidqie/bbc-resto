@props(['title' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @if($title)
        <div class="flex items-center gap-2 mb-4">
            @if($icon)
                <x-dynamic-component :component="$icon" class="w-4 h-4 text-gray-400" />
            @endif
            <h3 class="text-[15px] font-semibold text-gray-900 tracking-tight">{{ $title }}</h3>
        </div>
    @endif
    
    <div>
        {{ $slot }}
    </div>
</div>
