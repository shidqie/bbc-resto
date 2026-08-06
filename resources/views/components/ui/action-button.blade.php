@props(['title' => ''])

<button
    {{ $attributes->merge(['type' => 'button', 'title' => $title, 'class' => 'text-gray-500 transition hover:text-gray-900']) }}
>
    {{ $slot }}
</button>
