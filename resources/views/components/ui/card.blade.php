@props([
    'class' => ''
])

<div {{ $attributes->merge(['class' => "bg-surface rounded-md shadow-sm border border-gray-100 p-5 $class"]) }}>
    {{ $slot }}
</div>
