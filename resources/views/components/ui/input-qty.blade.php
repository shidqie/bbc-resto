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
    'stepper' => false,
])

@php
    $inputId = $id ?: $name;
    $inputClasses = "min-w-0 h-10 border border-primary/10 bg-surface text-sm font-semibold text-body text-center focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition";
@endphp

<div class="space-y-1 w-full"
     x-data="{
        val: '{{ $value ?: $min }}',
        min: parseInt('{{ $min }}', 10),
        max: parseInt('{{ $max ?: 9999 }}', 10),
        inc() { this.setVal((parseInt(this.val) || this.min) + 1); },
        dec() { this.setVal((parseInt(this.val) || this.min) - 1); },
        setVal(v) {
            if (isNaN(v)) v = this.min;
            if (v < this.min) v = this.min;
            if (v > this.max) v = this.max;
            this.val = String(v);
        }
     }">

    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-bold text-body">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <div class="w-full">
        @if($stepper)
            <div class="flex items-center w-full {{ $class }}">
                <button type="button" @click="dec()"
                        class="w-10 h-10 shrink-0 flex items-center justify-center border border-r-0 border-primary/10 bg-surface text-body rounded-l-xl hover:bg-primary/5 hover:text-primary transition-colors focus:outline-none"
                        aria-label="Kurangi jumlah">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/></svg>
                </button>
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="4"
                    id="{{ $inputId }}"
                    name="{{ $name }}"
                    value="{{ $value ?: $min }}"
                    x-model="val"
                    @input="let raw = $event.target.value.replace(/[^0-9]/g, ''); if(raw.length > 4) raw = raw.slice(0, 4); let v = parseInt(raw, 10); if(isNaN(v)) { val = ''; } else { setVal(v); }"
                    @blur="setVal(val)"
                    placeholder="{{ $placeholder }}"
                    @if($required) required @endif
                    {{ $attributes->merge(['class' => $inputClasses . ($error ? ' border-danger focus:border-danger' : '')]) }}
                >
                <button type="button" @click="inc()"
                        class="w-10 h-10 shrink-0 flex items-center justify-center border border-l-0 border-primary/10 bg-surface text-body rounded-r-xl hover:bg-primary/5 hover:text-primary transition-colors focus:outline-none"
                        aria-label="Tambah jumlah">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12M6 12h12"/></svg>
                </button>
            </div>
        @else
            <input
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="4"
                id="{{ $inputId }}"
                name="{{ $name }}"
                value="{{ $value ?: $min }}"
                x-model="val"
                @input="let raw = $event.target.value.replace(/[^0-9]/g, ''); if(raw.length > 4) raw = raw.slice(0, 4); let v = parseInt(raw, 10); if(isNaN(v)) { val = ''; } else if(v > max) { val = max; } else { val = v; }"
                @blur="if(!val || parseInt(val, 10) < min) val = min"
                placeholder="{{ $placeholder }}"
                @if($required) required @endif
                {{ $attributes->merge(['class' => "w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body placeholder-body/30 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-surface $class" . ($error ? ' border-danger focus:border-danger' : '')]) }}
            >
        @endif
    </div>

    @if($error)
        <p class="text-xs font-medium text-danger mt-1">{{ $error }}</p>
    @endif
</div>