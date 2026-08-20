@props([
    'name' => '',
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => '-- Pilih --',
    'required' => false,
    'class' => '',
    'onchange' => null,
])

@php
    $inputUniqueId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) . '_' . uniqid() : 'select_' . uniqid());
    
    // Normalize options into array of ['value' => ..., 'label' => ..., 'sub' => ...]
    $normalizedOptions = [];
    foreach ($options as $key => $val) {
        if (is_array($val) && isset($val['value'])) {
            $normalizedOptions[] = [
                'value' => (string) $val['value'],
                'label' => (string) ($val['label'] ?? $val['value']),
                'sub' => isset($val['sub']) ? (string)$val['sub'] : '',
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string) $key,
                'label' => (string) $val,
                'sub' => '',
            ];
        }
    }
    
    $selectedVal = is_null($selected) ? '' : (string) $selected;
@endphp

<div class="relative {{ $class }}" 
     x-data="{
        open: false,
        search: '',
        selectedId: @js($selectedVal),
        selectedName: '',
        options: @js($normalizedOptions),
        init() {
            if (this.selectedId !== '') {
                const found = this.options.find(o => String(o.value) === String(this.selectedId));
                if (found) {
                    this.selectedName = found.label;
                    this.search = found.label;
                }
            }
        },
        get filteredOptions() {
            if (!this.search || this.search === this.selectedName) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o => 
                (o.label && o.label.toLowerCase().includes(q)) || 
                (o.sub && o.sub.toLowerCase().includes(q)) ||
                (o.value && o.value.toLowerCase().includes(q))
            );
        },
        selectOption(opt) {
            this.selectedId = String(opt.value);
            this.selectedName = opt.label;
            this.search = opt.label;
            this.open = false;
            
            const hiddenInput = this.$refs.hiddenInput;
            hiddenInput.value = this.selectedId;
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            
            @if($onchange)
                const fn = new Function('value', 'label', 'option', @js($onchange));
                fn(this.selectedId, this.selectedName, opt);
            @endif
        },
        toggleOpen() {
            this.open = !this.open;
        }
     }"
     @click.outside="open = false; if(!selectedId) search = ''; else search = selectedName;">

    <input type="hidden" 
           name="{{ $name }}" 
           id="{{ $inputUniqueId }}" 
           x-ref="hiddenInput" 
           :value="selectedId" 
           @if($required) required @endif>

    <div class="relative">
        <input type="text"
               x-model="search"
               @focus="open = true"
               @click="open = true"
               @input="open = true; selectedId = ''; $refs.hiddenInput.value = '';"
               placeholder="{{ $placeholder }}"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white font-medium pr-10 cursor-pointer transition-all outline-none">

        <button type="button" @click.prevent="toggleOpen()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
    </div>

    {{-- Dropdown list panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-gray-50 left-0"
         style="display: none;">
        
        <template x-for="opt in filteredOptions" :key="opt.value">
            <div @click="selectOption(opt)"
                 class="px-3.5 py-2 hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between transition-colors group">
                <div>
                    <span class="text-sm font-semibold text-gray-800 group-hover:text-emerald-800" x-text="opt.label"></span>
                    <template x-if="opt.sub">
                        <span class="text-xs text-gray-400 block font-mono" x-text="opt.sub"></span>
                    </template>
                </div>
                <span x-show="String(opt.value) === String(selectedId)" class="text-emerald-600 font-bold text-xs">✓</span>
            </div>
        </template>

        <div x-show="filteredOptions.length === 0" class="px-3.5 py-2.5 text-xs text-gray-400 text-center">
            Pilihan tidak ditemukan
        </div>
    </div>
</div>
