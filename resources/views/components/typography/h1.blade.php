@props(['class' => ''])

<h1 {{ $attributes->merge(['class' => "font-serif text-3xl md:text-4xl lg:text-[40px] font-bold text-gray-900 tracking-tight leading-tight $class"]) }}>
    {{ $slot }}
</h1>
