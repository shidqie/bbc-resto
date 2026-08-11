@props([
    'label' => '',
    'id' => '',
    'name' => '',
    'value' => '1',
    'placeholder' => '1',
    'error' => '',
    'class' => '',
    'required' => false,
    'min' => '1',
    'max' => '9999',
])

@php
    $inputId = $id ?: $name;
@endphp

<div class="space-y-1 w-full" 
     x-data="{
        val: '{{ $value ?: $min }}',
        min: parseInt('{{ $min }}', 10),
        max: parseInt('{{ $max ?: 9999 }}', 10)
     }">
     
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-bold text-gray-700 font-sans">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif
    
    <div class="w-full">
        <input 
            type="text" 
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="4"
            id="{{ $inputId }}" 
            name="{{ $name }}" 
            x-model="val"
            @input="let raw = $event.target.value.replace(/[^0-9]/g, ''); if(raw.length > 4) raw = raw.slice(0, 4); let v = parseInt(raw, 10); if(isNaN(v)) { val = ''; } else if(v > max) { val = max; } else { val = v; }" 
            @blur="if(!val || parseInt(val, 10) < min) val = min"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge(['class' => "w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-900 placeholder-gray-300 focus:outline-none focus:ring-1 focus:ring-[#0D3024] focus:border-[#0D3024] transition bg-white $class" . ($error ? ' border-red-300 focus:border-red-500' : '')]) }}
        >
    </div>
    
    @if($error)
        <p class="text-xs font-medium text-red-500 mt-1">{{ $error }}</p>
    @endif
</div>
