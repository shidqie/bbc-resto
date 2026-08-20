@props([
    'label' => '',
    'id' => '',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'error' => '',
    'class' => '',
    'required' => false,
    'min' => '1',
    'max' => '99999',
    'stepper' => false,
])

@php
    $inputId = $id ?: $name;
    $inputClasses = "w-full min-w-0 h-10 border border-primary/10 bg-surface text-sm font-semibold text-body text-center focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none";
    $initialValue = $value !== '' ? $value : $min;
@endphp

<div class="space-y-1 w-full"
     x-data="{
        val: '{{ $initialValue }}',
        min: parseInt('{{ $min }}', 10) || 1,
        max: parseInt('{{ $max ?: 99999 }}', 10) || 99999,
        inc() {
            let n = parseInt(this.val, 10);
            if (isNaN(n) || n < this.min) n = this.min;
            else n++;
            if (n > this.max) n = this.max;
            this.val = String(n);
            this.notify();
        },
        dec() {
            let n = parseInt(this.val, 10);
            if (isNaN(n) || n <= this.min) n = this.min;
            else n--;
            this.val = String(n);
            this.notify();
        },
        handleBlur() {
            let n = parseInt(this.val, 10);
            if (isNaN(n) || n < this.min) {
                this.val = String(this.min);
            } else if (n > this.max) {
                this.val = String(this.max);
            }
            this.notify();
        },
        notify() {
            $nextTick(() => {
                let el = $el.querySelector('input');
                if (el) el.dispatchEvent(new Event('input', { bubbles: true }));
            });
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
                        class="w-10 h-10 shrink-0 flex items-center justify-center border border-r-0 border-primary/10 bg-surface text-body rounded-l-xl hover:bg-primary/5 hover:text-primary transition-colors focus:outline-none select-none"
                        aria-label="Kurangi jumlah">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/></svg>
                </button>
                <input
                    type="number"
                    min="{{ $min }}"
                    max="{{ $max }}"
                    id="{{ $inputId }}"
                    name="{{ $name }}"
                    x-model="val"
                    @blur="handleBlur()"
                    placeholder="{{ $placeholder ?: $min }}"
                    @if($required) required @endif
                    {{ $attributes->merge(['class' => $inputClasses . ($error ? ' border-danger focus:border-danger' : '')]) }}
                >
                <button type="button" @click="inc()"
                        class="w-10 h-10 shrink-0 flex items-center justify-center border border-l-0 border-primary/10 bg-surface text-body rounded-r-xl hover:bg-primary/5 hover:text-primary transition-colors focus:outline-none select-none"
                        aria-label="Tambah jumlah">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12M6 12h12"/></svg>
                </button>
            </div>
        @else
            <input
                type="number"
                min="{{ $min }}"
                max="{{ $max }}"
                id="{{ $inputId }}"
                name="{{ $name }}"
                x-model="val"
                @blur="handleBlur()"
                placeholder="{{ $placeholder ?: $min }}"
                @if($required) required @endif
                {{ $attributes->merge(['class' => "w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body placeholder-body/30 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-surface $class" . ($error ? ' border-danger focus:border-danger' : '')]) }}
            >
        @endif
    </div>

    @if($error)
        <p class="text-xs font-medium text-danger mt-1">{{ $error }}</p>
    @endif
</div>