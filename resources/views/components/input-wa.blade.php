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

<div
    x-data="{
        raw: @js($initial),
        touched: false,
        get digits() { return (this.raw || '').replace(/\D/g, ''); },
        get normalized() {
            let d = this.digits;
            if (d.startsWith('62')) d = '0' + d.slice(2);
            else if (d.startsWith('8')) d = '0' + d;
            return d;
        },
        get valid() { return /^08\d{8,12}$/.test(this.normalized); },
        sanitize() { this.touched = true; this.raw = (this.raw || '').replace(/[^\d+]/g, ''); },
        normalize() { if (this.valid) this.raw = this.normalized; },
    }"
    class="w-full">
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1.5">
        {{ $label }}
        @if($required)<span class="text-red-500">*</span>@endif
    </label>

    <input
        id="{{ $name }}"
        type="tel"
        name="{{ $name }}"
        x-model="raw"
        @input="sanitize()"
        @blur="normalize()"
        inputmode="numeric"
        autocomplete="tel"
        pattern="[0-9+]{10,16}"
        maxlength="16"
        value="{{ $initial }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        class="w-full px-4 py-3 bg-white border rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] {{ $error ? 'border-red-300' : 'border-gray-200' }} [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">

    <template x-if="valid">
        <p class="text-xs text-emerald-600 font-medium mt-1.5 flex items-center gap-1">
            <x-heroicon-o-check-circle class="w-3.5 h-3.5" /> Nomor WhatsApp valid <span x-text="'(' + normalized + ')'"></span>
        </p>
    </template>
    <template x-if="touched && !valid">
        <p class="text-xs text-red-500 font-medium mt-1.5 flex items-center gap-1">
            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5" /> Nomor WhatsApp tidak valid. Contoh: 081234567890 (10-14 digit)
        </p>
    </template>

    @if($hint)
        <p class="text-xs text-gray-400 mt-1.5">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="text-xs text-red-500 font-medium mt-1.5 flex items-center gap-1">
            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5" /> {{ $error }}
        </p>
    @endif
</div>
