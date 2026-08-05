@props([
    'name' => 'nomor_telepon',
    'label' => 'Nomor WhatsApp',
    'value' => '',
    'required' => false,
    'placeholder' => '08xxxxxxxxxx',
    'hint' => null,
])

@props([
    'name' => 'nomor_telepon',
    'label' => 'Nomor WhatsApp',
    'value' => '',
    'required' => false,
    'placeholder' => '08xxxxxxxxxx',
    'hint' => null,
])

@php
    $error = $errors->first($name);
    $initial = old($name, $value);
@endphp

<div class="w-full">
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1.5">
        {{ $label }}
        @if($required)<span class="text-red-500">*</span>@endif
    </label>

    <input
        id="{{ $name }}"
        type="text"
        inputmode="numeric"
        pattern="[0-9]*"
        maxlength="15"
        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
        name="{{ $name }}"
        value="{{ $initial }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        class="w-full px-4 py-3 bg-white border rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] {{ $error ? 'border-red-300' : 'border-gray-200' }}">

    @if($hint)
        <p class="text-xs text-gray-400 mt-1.5">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="text-xs text-red-500 font-medium mt-1.5 flex items-center gap-1">
            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5" /> {{ $error }}
        </p>
    @endif
</div>
