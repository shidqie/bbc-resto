@props([
    'label' => '',
    'id' => '',
    'name' => '',
    'value' => '',
    'placeholder' => '08...',
    'error' => '',
    'class' => '',
    'required' => false,
])

@php
    $inputId = $id ?: $name;
@endphp

<div class="space-y-1.5 w-full"
     x-data="{
        val: '{{ $value }}',
        format(v) {
            this.val = String(v).replace(/[^0-9]/g, '');
        }
     }">
     
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700 font-sans">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    
    <input 
        type="text" 
        id="{{ $inputId }}" 
        name="{{ $name }}" 
        x-model="val" 
        @input="format($event.target.value)"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        minlength="10"
        maxlength="15"
        {{ $attributes->merge(['class' => "block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-base $class" . ($error ? ' border-danger focus:border-danger focus:ring-danger/20' : '')]) }}
    >
    
    @if($error)
        <p class="text-xs font-medium text-danger mt-1">{{ $error }}</p>
    @endif
</div>
