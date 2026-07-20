@props(['class' => ''])

<h3 {{ $attributes->merge(['class' => "font-serif text-lg md:text-xl lg:text-2xl font-semibold text-gray-900 leading-snug $class"]) }}>
    {{ $slot }}
</h3>
