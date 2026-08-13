@props(['items' => [], 'totalLabel' => 'Total Keseluruhan', 'totalValue' => 'Rp0'])

<div {{ $attributes->merge(['class' => 'bg-gray-50/50 rounded-xl p-5 space-y-3']) }}>
    @foreach($items as $item)
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500 font-medium">{{ $item['label'] }}</span>
            <span class="text-gray-900 font-medium">{{ $item['value'] }}</span>
        </div>
    @endforeach
    
    @if(count($items) > 0)
        <div class="h-px bg-gray-200 my-4 w-full"></div>
    @endif

    <div class="flex items-center justify-between">
        <span class="text-sm font-bold text-gray-900">{{ $totalLabel }}</span>
        <span class="text-lg font-bold text-gray-900">{{ $totalValue }}</span>
    </div>
</div>
