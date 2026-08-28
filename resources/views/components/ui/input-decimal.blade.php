@props([
    'label' => '',
    'id' => '',
    'name' => '',
    'value' => '',
    'placeholder' => '0,00',
    'error' => '',
    'class' => '',
    'required' => false,
    'min' => '0',
    'step' => '0.01'
])

@php
    $inputId = $id ?: $name;
@endphp

<div class="space-y-1.5 w-full" 
     x-data="{
        rawValue: '{{ $value }}',
        displayValue: '',
        format(val) {
            if (val === '' || val === null || val === undefined) {
                this.displayValue = '';
                this.rawValue = '';
                return;
            }
            let str = String(val);
            if (str.endsWith(',')) {
                this.displayValue = str;
                this.rawValue = str.replace(',', '.');
                return;
            }
            let num = parseFloat(str.replace(',', '.'));
            if (isNaN(num)) {
                this.displayValue = '';
                this.rawValue = '';
                return;
            }
            let formatted = num.toString().replace('.', ',');
            this.displayValue = formatted;
            this.rawValue = num.toString();
        }
     }" 
     x-init="format(rawValue)"
     @value-updated.window="if($event.detail.id === '{{ $inputId }}') { format($event.detail.value); }">
     
    @if($label)
        <label for="{{ $inputId }}_display" class="block text-sm font-semibold text-gray-700 font-sans">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif
    
    <input 
        type="text" 
        id="{{ $inputId }}_display"
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
        step="{{ $step }}"
    >
    
    @if($error)
        <p class="text-xs font-medium text-danger mt-1">{{ $error }}</p>
    @endif
</div>
