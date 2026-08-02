{{--
|--------------------------------------------------------------------------
| Tab List Component
|--------------------------------------------------------------------------
| Container tab minimalist — seragam di seluruh aplikasi (POS, Manajemen Menu, dll).
|
| Props:
|   - $align (string) : 'left' | 'right' (posisi tab)
|
| Contoh:
|   <x-ui.tab-list align="right">
|       <x-ui.tab href="..." :active="true">Menu Dine In</x-ui.tab>
|       <x-ui.tab @click="view = 'menu'">Katalog Menu</x-ui.tab>
|   </x-ui.tab-list>
|--}}

@props(['align' => 'left'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-6 border-b border-gray-200' . ($align === 'right' ? ' justify-end' : '')]) }}>
    {{ $slot }}
</div>
