{{--
|--------------------------------------------------------------------------
| Search Input Component
|--------------------------------------------------------------------------
| Input pencarian seragam. Harus di dalam <form method="GET">.
--}}

@props([
    'name'        => 'search',
    'value'       => '',
    'placeholder' => 'Cari data…',
    'width'       => 'w-full sm:w-64',
])

<div class="relative {{ $width }} shrink-0">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
    </span>
    <input
        type="text"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 outline-none transition-all focus:border-gray-900 focus:ring-1 focus:ring-gray-900/20 hover:border-gray-300']) }}
    >
</div>