@props(['class' => ''])

<h2 {{ $attributes->merge(['class' => "font-serif text-2xl md:text-3xl lg:text-[32px] font-bold text-gray-900 tracking-tight leading-snug $class"]) }}>
    {{ $slot }}
</h2>
