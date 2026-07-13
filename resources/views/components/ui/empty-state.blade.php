{{--
|--------------------------------------------------------------------------
| Empty State Component
|--------------------------------------------------------------------------
| Komponen placeholder ketika tabel/list kosong (tidak ada data).
|
| Props:
|   - icon    (string) : Kelas icon FontAwesome (default: "fa-box-open")
|   - title   (string) : Judul pesan kosong (default: "Tidak ada data")
|   - message (string) : Deskripsi tambahan (opsional)
|
| Slots:
|   - default : Tombol aksi tambahan (opsional, misal "Tambah Data")
|
| Contoh Pemakaian:
|   <x-ui.empty-state icon="fa-receipt" title="Belum ada pesanan" message="Buat pesanan baru">
|       <a href="/pesanan/create">Buat Pesanan</a>
|   </x-ui.empty-state>
--}}

@props([
    'icon'    => 'fa-box-open',
    'title'   => 'Tidak ada data',
    'message' => '',
])

<div class="px-6 py-12 text-center">
    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
        <i class="fas {{ $icon }} text-2xl"></i>
    </div>
    <h3 class="text-gray-900 font-medium mb-1">{{ $title }}</h3>
    @if($message)
        <p class="text-gray-500 text-sm mb-4">{{ $message }}</p>
    @endif
    
    {{-- Slot untuk tombol aksi tambahan --}}
    @if(!$slot->isEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
