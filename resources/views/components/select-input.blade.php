{{--
|--------------------------------------------------------------------------
| Select Input Component (untuk Filter)
|--------------------------------------------------------------------------
| Dropdown/select seragam untuk filter tabel.
--}}

@props([
    'name'        => '',
    'options'     => [],
    'selected'    => null,
    'placeholder' => 'Semua',
    'blankValue'  => '',
    'autoSubmit'  => false,
    'form'        => null,
    'width'       => '',
])

<div class="relative {{ $width }} shrink-0">
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if($form) form="{{ $form }}" @endif
        @if($autoSubmit) onchange="this.form.submit()" @endif
        {{ $attributes->merge(['class' => 'w-full appearance-none rounded-lg border border-gray-200 bg-surface py-2.5 pl-3.5 pr-9 text-sm text-gray-700 outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary/20 hover:border-gray-300']) }}
    >
        <option value="{{ $blankValue }}">{{ $placeholder }}</option>
        @foreach($options as $key => $label)
            @php
                if (is_array($label) && isset($label['value'], $label['label'])) {
                    $optValue = $label['value'];
                    $optLabel = $label['label'];
                } else {
                    $optValue = $key;
                    $optLabel = $label;
                }
            @endphp
            <option value="{{ $optValue }}" @selected((string) $selected === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
        <x-heroicon-o-chevron-down class="w-4 h-4" />
    </span>
</div>