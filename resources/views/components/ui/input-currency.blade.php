@props([
    'label' => '',
    'id' => '',
    'name' => '',
    'value' => '',
    'placeholder' => '0',
    'error' => '',
    'class' => '',
    'required' => false,
    'min' => '0',
])

@php
    $inputId = $id ?: $name;
@endphp

<div class="space-y-1.5 w-full" 
     x-data="{
        rawValue: '{{ $value }}',
        displayValue: '',
        format(val) {
            let num = String(val).replace(/[^0-9]/g, '');
            this.rawValue = num;
            this.displayValue = num ? 'Rp ' + parseInt(num, 10).toLocaleString('id-ID') : '';
        }
     }" 
     x-init="format(rawValue)">
     
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700 font-sans">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    
    <input 
        type="text" 
        x-model="displayValue" 
        @input="format($event.target.value)" 
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => "block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-base $class" . ($error ? ' border-danger focus:border-danger focus:ring-danger/20' : '')]) }}
    >
    
    <input 
        type="hidden" 
        id="{{ $inputId }}" 
        name="{{ $name }}" 
        :value="rawValue"
        @if($required) required @endif
        min="{{ $min }}"
    >
    
    @if($error)
        <p class="text-xs font-medium text-danger mt-1">{{ $error }}</p>
    @endif
</div>
