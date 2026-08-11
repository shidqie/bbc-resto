@props([
    'label' => '',
    'id' => '',
    'type' => 'text',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'error' => '',
    'class' => ''
])

@php
    $inputId = $id ?: $name;
@endphp

<div class="space-y-1 w-full">
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-bold text-gray-700 font-sans">
            {{ $label }}
        </label>
    @endif
    
    <input 
        type="{{ $type }}" 
        id="{{ $inputId }}" 
        name="{{ $name }}" 
        value="{{ $value }}" 
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => "block w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-medium text-gray-900 placeholder-gray-300 shadow-2xs focus:border-[#0D3024] focus:bg-white focus:ring-1 focus:ring-[#0D3024]/20 transition-all duration-200 outline-none $class" . ($error ? ' border-danger focus:border-danger' : '')]) }}
    >
    
    @if($error)
        <p class="text-xs font-medium text-danger mt-1">{{ $error }}</p>
    @endif
</div>
