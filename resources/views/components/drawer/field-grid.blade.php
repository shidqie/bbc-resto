@props(['cols' => 2])

@php
    $gridCols = match((int)$cols) {
        1 => 'grid-cols-1',
        3 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4',
        default => 'grid-cols-1 sm:grid-cols-2', // Default 2 cols
    };
@endphp

<div {{ $attributes->merge(['class' => 'grid gap-6 ' . $gridCols]) }}>
    {{ $slot }}
</div>
