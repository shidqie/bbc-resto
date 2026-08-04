{{--
|--------------------------------------------------------------------------
| Empty State Component
|--------------------------------------------------------------------------
| Placeholder konsisten untuk tabel/list yang kosong.
|
| Props:
|   - icon    (string) : Nama ikon heroicon (default: "document-text")
|   - title   (string) : Judul pesan (default: "Belum ada data")
|   - message (string) : Deskripsi tambahan (opsional)
|   - colspan (int)    : Jumlah kolom tabel (agar sel merentang penuh)
|
| Slots:
|   - default : Konten tambahan (mis. tombol aksi)
|
| Contoh:
|   <x-empty-state icon="cube" title="Belum ada bahan baku"
|       message="Tambahkan bahan baku pertama Anda" colspan="9" />
|--------------------------------------------------------------------------------
--}}

@props([
    'icon'    => 'document-text',
    'title'   => 'Belum ada data',
    'message' => '',
    'colspan' => 0,
])

@php
    $icons = [
        'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
        'cube'          => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
        'archive-box'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />',
        'users'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
        'chat-bubble'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />',
        'clipboard'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />',
        'clock'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'exclamation'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
    ];
    $iconPath = $icons[$icon] ?? $icons['document-text'];
@endphp

<tr>
    <td colspan="{{ $colspan > 0 ? $colspan : '1' }}" class="px-4 py-14 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $iconPath !!}</svg>
        </div>
        <h3 class="mt-4 text-sm font-semibold text-gray-700">{{ $title }}</h3>
        @if($message)
            <p class="mt-1 text-xs text-gray-400">{{ $message }}</p>
        @endif
        @if(!$slot->isEmpty())
            <div class="mt-4 flex justify-center">{{ $slot }}</div>
        @endif
    </td>
</tr>