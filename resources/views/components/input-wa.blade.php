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
    <label for="{{ $name }}" class="block text-xs font-bold text-gray-700 mb-1">
        {{ $label }}
        @if($required)<span class="text-red-500">*</span>@endif
    </label>

    <input
        id="{{ $name }}"
        type="text"
        inputmode="numeric"
        pattern="[0-9]*"
        maxlength="15"
        oninput="let v = this.value.replace(/[^0-9]/g, ''); if(v.startsWith('62')) v = '0' + v.substring(2); if(v.length > 0 && v[0] !== '0') v = '0' + v; if(v.length > 1 && v[1] !== '8') v = '08' + v.substring(1); this.value = v;"
        name="{{ $name }}"
        value="{{ $initial }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        class="w-full px-3.5 py-2 bg-white border rounded-xl text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/20 outline-none {{ $error ? 'border-red-300' : 'border-gray-200' }}">

    @if($hint)
        <p class="text-[11px] text-gray-400 font-medium mt-1">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="text-xs text-red-500 font-medium mt-1.5 flex items-center gap-1">
            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5" /> {{ $error }}
        </p>
    @endif
</div>
