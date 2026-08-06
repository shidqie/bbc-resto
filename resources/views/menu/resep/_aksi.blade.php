@php
    $aksiId = $aksiMenuId ?? null;
    $aksiNama = $aksiNamaMenu ?? '';
    $aksiIsPaket = $aksiIsPaket ?? false;
    $aksiUsed = $aksiUsed ?? false;
    $aksiStatus = $aksiStatus ?? true;
    $aksiRoute = $aksiIsPaket ? 'paket-catering' : 'menu';
@endphp
@if($aksiId)
<div class="flex items-center justify-center gap-1.5">
    {{-- Tombol utama: Atur Resep / Atur Komposisi --}}
    @if($aksiIsPaket)
        <button onclick="openKomposisiForm({{ $aksiId }})" title="Atur Komposisi" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-amber-700 rounded-lg hover:bg-amber-800 transition-colors">
            <x-heroicon-o-squares-2x2 class="w-3.5 h-3.5" />
            Atur Komposisi
        </button>
    @else
        <button onclick="openResepForm({{ $aksiId }}, false)" title="Atur Resep" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors">
            <x-heroicon-o-clipboard-document-list class="w-3.5 h-3.5" />
            Atur Resep
        </button>
    @endif

    {{-- Detail --}}
    @if($aksiIsPaket)
        <a href="{{ route('paket-catering.show', $aksiId) }}" title="Lihat Detail Paket" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
            <x-heroicon-o-eye class="w-4 h-4" />
        </a>
    @else
        <a href="{{ route('menu.show', $aksiId) }}" title="Lihat Detail Menu" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
            <x-heroicon-o-eye class="w-4 h-4" />
        </a>
    @endif

    {{-- Edit --}}
    @if($aksiIsPaket)
        <a href="{{ route('paket-catering.edit', $aksiId) }}" title="Edit Paket" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
        </a>
    @else
        <a href="{{ route('menu.edit', $aksiId) }}" title="Edit Menu" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
        </a>
    @endif

    {{-- Nonaktifkan / Aktifkan --}}
    @if($aksiIsPaket)
        <form action="{{ route('paket-catering.toggle', $aksiId) }}" method="POST" class="inline">
            @csrf @method('PATCH')
            <x-ui.action-button type="submit" title="{{ $aksiStatus ? 'Nonaktifkan Paket' : 'Aktifkan Paket' }}">
                <x-heroicon-o-power class="w-4 h-4" />
            </x-ui.action-button>
        </form>
    @else
        <form action="{{ route('menu.toggle', $aksiId) }}" method="POST" class="inline">
            @csrf @method('PATCH')
            <x-ui.action-button type="submit" title="{{ $aksiStatus ? 'Nonaktifkan Menu' : 'Aktifkan Menu' }}">
                <x-heroicon-o-power class="w-4 h-4" />
            </x-ui.action-button>
        </form>
    @endif

    {{-- Hapus (hanya bila belum pernah digunakan) --}}
    @if(!$aksiUsed)
        <form action="{{ $aksiIsPaket ? route('paket-catering.destroy', $aksiId) : route('menu.destroy', $aksiId) }}" method="POST" onsubmit="return confirmHapusMenu(event, '{{ $aksiNama }}')" class="inline">
            @csrf @method('DELETE')
            <x-ui.action-button type="submit" title="Hapus {{ $aksiIsPaket ? 'Paket' : 'Menu' }}">
                <x-heroicon-o-trash class="w-4 h-4" />
            </x-ui.action-button>
        </form>
    @endif
</div>
@endif
