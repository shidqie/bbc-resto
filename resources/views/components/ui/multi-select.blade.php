@props([
    'name' => '',
    'options' => [],
    'selected' => [],
    'label' => 'Filter',
    'type' => 'checkbox',
])

@php
    if ($type === 'radio') {
        $selectedVal = is_array($selected) ? ($selected[0] ?? '') : strval($selected);
        $selectedJson = json_encode($selectedVal);
    } else {
        $selectedArray = array_map('strval', (array) $selected);
        $selectedJson = json_encode($selectedArray);
    }
    
    // Support [['value' => '...', 'label' => '...']] or ['value' => 'label']
    $formattedOptions = [];
    foreach ($options as $key => $val) {
        if (is_array($val) && isset($val['value'], $val['label'])) {
            $formattedOptions[(string)$val['value']] = $val['label'];
        } else {
            $formattedOptions[(string)$key] = $val;
        }
    }
@endphp

<div x-data="{ 
        open: false, 
        selected: {{ $selectedJson }},
        get count() {
            if (Array.isArray(this.selected)) {
                return this.selected.filter(x => x && x !== 'all' && x !== 'semua' && x !== 'Semua' && x !== '').length;
            }
            return (this.selected && this.selected !== 'all' && this.selected !== 'semua' && this.selected !== 'Semua' && this.selected !== '') ? 1 : 0;
        }
    }" 
    class="relative shrink-0"
    @click.away="open = false">
    
    <button type="button" @click="open = !open" 
        class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-900/20 transition-all">
        <span>{{ $label }}</span>
        
        <span x-show="count > 0" x-text="count" style="display: none;" 
              class="flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary rounded-full shadow-sm">
        </span>
        
        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 transition-transform duration-200" x-bind:class="{'rotate-180': open}" />
    </button>

    <div x-show="open" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-[100] w-56 py-2 mt-2 bg-white border border-gray-100 shadow-xl rounded-xl left-0"
        style="display: none;">
        
        <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilih {{ strtolower($label) }}</div>
        
        <div class="flex flex-col gap-1 px-2 max-h-64 overflow-y-auto">
            @foreach($formattedOptions as $val => $text)
                <label class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors group">
                    <div class="relative flex items-center justify-center w-5 h-5">
                        <input type="{{ $type }}" name="{{ $name }}{{ $type === 'checkbox' ? '[]' : '' }}" value="{{ $val }}" 
                            class="peer absolute w-5 h-5 opacity-0 cursor-pointer"
                            x-model="selected"
                            @change="$el.closest('form').submit()">
                        <div class="w-5 h-5 border-2 border-gray-300 {{ $type === 'radio' ? 'rounded-full' : 'rounded' }} bg-white peer-checked:bg-emerald-500 peer-checked:border-emerald-500 flex items-center justify-center transition-colors group-hover:border-emerald-400">
                            @if($type === 'radio')
                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            @else
                                <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                    </div>
                    <span class="font-medium group-hover:text-emerald-700 transition-colors">{{ $text }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
