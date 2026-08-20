{{--
|--------------------------------------------------------------------------
| Input Component
|--------------------------------------------------------------------------
| Props:
|   - label, id, type, name, value, placeholder, error, helper, class
--}}

@props([
    'label'       => '',
    'id'          => '',
    'type'        => 'text',
    'name'        => '',
    'value'       => '',
    'placeholder' => '',
    'error'       => '',
    'helper'      => '',
    'class'       => '',
])

@php
    $inputId = $id ?: $name;
@endphp

<div class="space-y-1 w-full">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => "block w-full rounded-lg border border-gray-200 bg-surface px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all duration-150 outline-none $class" . ($error ? ' border-red-400 focus:border-red-500 focus:ring-red-500/20' : '')]) }}
    >

    @if($helper && !$error)
        <p class="text-xs text-gray-500 mt-0.5">{{ $helper }}</p>
    @endif

    @if($error)
        <p class="text-xs font-medium text-red-600 mt-0.5">{{ $error }}</p>
    @endif
</div>
