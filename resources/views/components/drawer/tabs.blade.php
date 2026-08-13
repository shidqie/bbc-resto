@props(['tabs', 'activeTab' => ''])

<div class="flex px-6 border-b border-gray-100 shrink-0 overflow-x-auto hide-scrollbar">
    @foreach($tabs as $key => $label)
        <button type="button" 
                @click="{{ $activeTab }} = '{{ $key }}'" 
                :class="{{ $activeTab }} === '{{ $key }}' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="py-3 px-1 mr-6 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none whitespace-nowrap">
            {{ $label }}
        </button>
    @endforeach
</div>
