{{--
|--------------------------------------------------------------------------
| Search Input Component (Realtime Debounced Auto-Submit)
|--------------------------------------------------------------------------
| Input pencarian seragam. Otomatis mencari secara realtime saat diketik.
--}}

@props([
    'name'        => 'search',
    'value'       => '',
    'placeholder' => 'Cari data…',
    'width'       => 'w-full sm:w-64',
    'debounce'    => 400,
])

<div class="relative {{ $width }} shrink-0"
     x-data="{
         timer: null,
         handleInput(e) {
             clearTimeout(this.timer);
             this.timer = setTimeout(() => {
                 let form = e.target.closest('form');
                 if (form) {
                     if (typeof form.requestSubmit === 'function') {
                         form.requestSubmit();
                     } else {
                         form.submit();
                     }
                 }
             }, {{ $debounce }});
         }
     }"
     x-init="
         $nextTick(() => {
             let inp = $el.querySelector('input');
             if (inp && inp.value && document.activeElement !== inp && location.search.includes('{{ $name }}=')) {
                 inp.focus();
                 let len = inp.value.length;
                 inp.setSelectionRange(len, len);
             }
         });
     ">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
    </span>
    <input
        type="text"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        @input="handleInput($event)"
        autocomplete="off"
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-200 bg-surface py-2.5 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary/20 hover:border-gray-300']) }}
    >
</div>