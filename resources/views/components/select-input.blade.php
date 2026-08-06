{{--
|--------------------------------------------------------------------------
| Select Input Component
|--------------------------------------------------------------------------
| Komponen dropdown/select yang seragam di seluruh halaman.
|
| Props:
|   - name        (string) : Nama field select (wajib)
|   - options     (array)  : Daftar opsi. Bisa berbentuk:
|                              ['1' => 'Label', '2' => 'Label']
|                              atau [['value' => '1', 'label' => 'Label'], ...]
|   - selected    (mixed)  : Nilai yang terpilih (biasanya request('key'))
|   - placeholder (string) : Opsi kosong pertama (default: "Semua")
|   - blankValue  (string) : Nilai untuk opsi kosong (default: "")
|   - autoSubmit  (bool)   : Jika true, form langsung dikirim saat berubah
|   - form        (string) : Atribut form="" (untuk select di luar form)
|   - width       (string) : Kelas lebar wrapper
|
| Gunakan di dalam <form method="GET"> untuk filter pencarian.
|--------------------------------------------------------------------------------
--}}

@props([
    'name'        => '',
    'options'     => [],
    'selected'    => null,
    'placeholder' => 'Semua',
    'blankValue'  => '',
    'autoSubmit'  => false,
    'form'        => null,
    'width'       => '',
])

<div class="relative {{ $width }} shrink-0">
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if($form) form="{{ $form }}" @endif
        @if($autoSubmit) onchange="this.form.submit()" @endif
        {{ $attributes->merge(['class' => 'w-full appearance-none rounded-xl border border-gray-200 bg-white py-2 pl-4 pr-10 text-sm text-gray-700 outline-none transition-all focus:border-blue-500 focus:ring-1 focus:ring-blue-500 hover:border-gray-300']) }}
    >
        <option value="{{ $blankValue }}">{{ $placeholder }}</option>
        @foreach($options as $key => $label)
            @php
                if (is_array($label) && isset($label['value'], $label['label'])) {
                    $optValue = $label['value'];
                    $optLabel = $label['label'];
                } else {
                    $optValue = $key;
                    $optLabel = $label;
                }
            @endphp
            <option value="{{ $optValue }}" @selected((string) $selected === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </span>
</div>