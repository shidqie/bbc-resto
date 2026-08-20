{{--
|--------------------------------------------------------------------------
| Tab Component
|--------------------------------------------------------------------------
| Satu item tab — otomatis jadi <a> bila ada href, <button> bila tidak.
| Gaya aktif: underline brand 2px (border-primary, font-bold).
|
| Props:
|   - $active (bool) : true = state aktif (untuk link statis)
|
| State dinamis (Alpine) cukup lewatkan :class langsung:
|   <x-ui.tab @click="leftView = 'menu'"
|             :class="leftView === 'menu' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-primary hover:border-primary/40'">
|--}}

@props(['active' => false])

@php
    $base = 'py-3 text-sm font-medium border-b-2 transition-colors ';
    $state = $active
        ? 'border-primary text-primary font-bold'
        : 'border-transparent text-gray-500 hover:text-primary hover:border-primary/40';
@endphp

@if($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $base . $state]) }}>{{ $slot }}</a>
@else
    <button type="button" {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</button>
@endif
