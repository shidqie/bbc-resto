<div class="overflow-hidden border border-gray-200 bg-white">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full table-auto text-left text-sm']) }}>
            {{ $slot }}
        </table>
    </div>
</div>
