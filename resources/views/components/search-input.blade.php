{{--
|--------------------------------------------------------------------------
| Search Input Component
|--------------------------------------------------------------------------
| Komponen input pencarian yang seragam di seluruh halaman.
|
| Props:
|   - name        (string) : Nama field input (default: "search")
|   - value       (mixed)  : Nilai saat ini (pengguna: request('search'))
|   - placeholder (string) : Placeholder (default: "Cari…")
|   - width       (string) : Kelas lebar (default: "w-full sm:w-64")
|
| Komponen harus berada di dalam elemen <form method="GET"> agar pencarian
| berfungsi saat tombol Enter ditekan. Ikon kaca pembesar tampil di kiri.
|--------------------------------------------------------------------------------
--}}

@props([
    'name'        => 'search',
    'value'       => '',
    'placeholder' => 'Cari data…',
    'width'       => 'w-full sm:w-64',
])

<div class="relative {{ $width }} shrink-0">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </span>
    <input
        type="text"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-gray-200 bg-white py-2 pl-10 pr-16 text-sm text-gray-700 placeholder-gray-400 outline-none transition-all focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 hover:border-gray-300']) }}
    >
    <button type="submit" class="absolute inset-y-1.5 right-1.5 px-3 flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-medium transition-colors">
        Cari
    </button>
</div>