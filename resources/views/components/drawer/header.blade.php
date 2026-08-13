@props([
    'title',
    'subtitle' => null,
])

<div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
    <div>
        <h2 class="text-lg font-semibold text-gray-900 leading-tight" id="drawer-title">
            {{ $title }}
        </h2>
        @if($subtitle)
            <p class="text-[13px] text-gray-500 mt-1">
                {{ $subtitle }}
            </p>
        @endif
    </div>
    <button @click="show = false" type="button" class="flex items-center justify-center w-10 h-10 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
        <span class="sr-only">Tutup panel</span>
        <x-heroicon-o-x-mark class="w-5 h-5" />
    </button>
</div>
