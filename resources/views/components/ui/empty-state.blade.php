{{--
|--------------------------------------------------------------------------
| Empty State Component
|--------------------------------------------------------------------------
| Placeholder ketika tabel/list kosong.
|
| Props:
|   - icon    (string) : Nama Heroicon outline (default: "archive-box")
|   - title   (string) : Judul pesan kosong
|   - message (string) : Deskripsi tambahan
|
| Slots:
|   - default : Tombol aksi (opsional)
--}}

@props([
    'icon'    => 'archive-box',
    'title'   => 'Belum Ada Data',
    'message' => 'Data belum tersedia.',
])

<div class="px-6 py-14 text-center">
    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
        <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-7 h-7" />
    </div>
    <h3 class="text-gray-900 font-medium text-sm mb-1">{{ $title }}</h3>
    @if($message)
        <p class="text-gray-500 text-sm mb-4">{{ $message }}</p>
    @endif

    @if(!$slot->isEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
