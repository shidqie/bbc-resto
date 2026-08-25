@props([
    'name' => '',
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Cari atau pilih...',
    'required' => false,
    'class' => '',
    'searchIcon' => true,
    'onchange' => null,
])

@php
    $inputUniqueId = $id ?? ($name ? str_replace(['[', ']', '.'], '_', $name) . '_' . uniqid() : 'select_' . uniqid());
    
    // Normalize options into array of ['value' => ..., 'label' => ..., 'sub' => ..., 'badge' => ...]
    $normalizedOptions = [];
    foreach ($options as $key => $val) {
        if (is_array($val) && isset($val['value'])) {
            $normalizedOptions[] = [
                'value' => (string) $val['value'],
                'label' => (string) ($val['label'] ?? $val['value']),
                'sub'   => isset($val['sub']) ? (string)$val['sub'] : (isset($val['kode']) ? (string)$val['kode'] : ''),
                'badge' => isset($val['badge']) ? (string)$val['badge'] : (isset($val['satuan']) ? (string)$val['satuan'] : ''),
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string) $key,
                'label' => (string) $val,
                'sub'   => '',
                'badge' => '',
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
                (o.badge && o.badge.toLowerCase().includes(q)) ||
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
        @if($searchIcon)
            {{-- Magnifying Glass SVG Icon --}}
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        @endif

        <input type="text"
               x-model="search"
               @focus="open = true"
               @click="open = true"
               @input="open = true; selectedId = ''; $refs.hiddenInput.value = '';"
               placeholder="{{ $placeholder }}"
               class="w-full h-10 rounded-xl border border-gray-200 bg-white text-sm {{ $searchIcon ? 'pl-10' : 'pl-3.5' }} pr-9 py-2 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all font-medium shadow-sm">

        <button type="button" @click.prevent="toggleOpen()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
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
         class="absolute z-50 mt-1.5 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-50 left-0"
         style="display: none;">
        
        <template x-for="opt in filteredOptions" :key="opt.value">
            <div @click="selectOption(opt)"
                 class="px-4 py-2.5 hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between transition-colors group">
                <div>
                    <p class="text-sm font-semibold text-gray-800 group-hover:text-emerald-900" x-text="opt.label"></p>
                    <template x-if="opt.sub">
                        <p class="text-xs text-gray-400 font-medium font-mono" x-text="opt.sub"></p>
                    </template>
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="opt.badge">
                        <span class="text-xs px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full font-medium" x-text="opt.badge"></span>
                    </template>
                    <span x-show="String(opt.value) === String(selectedId)" class="text-emerald-600 font-bold text-xs">✓</span>
                </div>
            </div>
        </template>

        <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-xs text-gray-400 text-center">
            Pilihan tidak ditemukan
        </div>
    </div>
</div>
