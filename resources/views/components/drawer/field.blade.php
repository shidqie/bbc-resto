@props(['label', 'value' => null, 'full' => false])

<div class="{{ $full ? 'col-span-full' : '' }}">
    <p class="text-[12px] font-medium text-gray-500 uppercase tracking-wider mb-1.5">{{ $label }}</p>
    @if(isset($value))
        <p class="text-sm font-medium text-gray-900 leading-relaxed">{{ $value }}</p>
    @else
        {{ $slot }}
    @endif
</div>
