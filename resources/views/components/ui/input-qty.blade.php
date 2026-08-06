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
    'max' => null,
])

@php
    $inputId = $id ?: $name;
@endphp

<div class="space-y-1.5 w-full" 
     x-data="{
        val: parseInt('{{ $value ?: $min }}', 10),
        min: parseInt('{{ $min }}', 10),
        max: {{ $max ? 'parseInt('.$max.', 10)' : 'null' }}
     }">
     
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700 font-sans">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    
    <div class="flex items-center w-full relative">
        <button type="button" @click="if(val > min) val--" class="absolute left-1 w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-900 transition-colors z-10">
            <x-heroicon-s-minus class="w-4 h-4" />
        </button>
        
        <input 
            type="number" 
            id="{{ $inputId }}" 
            name="{{ $name }}" 
            x-model="val"
            @input="let v = parseInt($event.target.value, 10); val = isNaN(v) ? min : Math.max(min, max !== null ? Math.min(v, max) : v)" 
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            min="{{ $min }}"
            @if($max) max="{{ $max }}" @endif
            {{ $attributes->merge(['class' => "block w-full rounded-xl border border-gray-200 bg-white px-12 py-3 text-center font-semibold text-gray-900 shadow-sm focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-base $class" . ($error ? ' border-danger focus:border-danger focus:ring-danger/20' : '')]) }}
            style="-moz-appearance: textfield; [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none;"
        >
        
        <button type="button" @click="if(max === null || val < max) val++" class="absolute right-1 w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-900 transition-colors z-10">
            <x-heroicon-s-plus class="w-4 h-4" />
        </button>
    </div>
    
    @if($error)
        <p class="text-xs font-medium text-danger mt-1">{{ $error }}</p>
    @endif
</div>
