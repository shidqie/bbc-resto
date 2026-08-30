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
        <button onclick="openResepForm({{ $aksiId }}, false)" title="Atur Resep" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors">
            <x-heroicon-o-clipboard-document-list class="w-3.5 h-3.5" />
            Atur Resep
        </button>
    @endif

    {{-- Detail --}}
    @if($aksiIsPaket)
        <x-ui.action-button href="{{ route('paket-catering.show', $aksiId) }}" title="Lihat Detail Paket" label="Detail">
            <x-heroicon-o-eye class="w-3.5 h-3.5" />
        </x-ui.action-button>
    @else
        <x-ui.action-button href="{{ route('menu.show', $aksiId) }}" title="Lihat Detail Menu" label="Detail">
            <x-heroicon-o-eye class="w-3.5 h-3.5" />
        </x-ui.action-button>
    @endif

    {{-- Edit --}}
    @if($aksiIsPaket)
        <x-ui.action-button href="{{ route('paket-catering.edit', $aksiId) }}" title="Edit Paket" label="Edit">
            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
        </x-ui.action-button>
    @else
        <x-ui.action-button href="{{ route('menu.edit', $aksiId) }}" title="Edit Menu" label="Edit">
            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
        </x-ui.action-button>
    @endif

    {{-- Dropdown Lainnya untuk Status dan Hapus (> 3 aksi) --}}
    <x-ui.action-dropdown>
        @if($aksiIsPaket)
            <form id="toggle-paket-{{ $aksiId }}" action="{{ route('paket-catering.toggle', $aksiId) }}" method="POST" class="hidden">
                @csrf @method('PATCH')
            </form>
            <x-ui.action-dropdown-item icon="power" label="{{ $aksiStatus ? 'Nonaktifkan' : 'Aktifkan' }}" variant="{{ $aksiStatus ? 'warning' : 'success' }}" onclick="document.getElementById('toggle-paket-{{ $aksiId }}').submit()" />
            @if(!$aksiUsed)
                <form id="delete-paket-{{ $aksiId }}" action="{{ route('paket-catering.destroy', $aksiId) }}" method="POST" onsubmit="return confirmHapusMenu(event, '{{ $aksiNama }}')" class="hidden">
                    @csrf @method('DELETE')
                </form>
                <x-ui.action-dropdown-item icon="trash" label="Hapus" variant="danger" onclick="document.getElementById('delete-paket-{{ $aksiId }}').submit()" />
            @endif
        @else
            <form id="toggle-menu-{{ $aksiId }}" action="{{ route('menu.toggle', $aksiId) }}" method="POST" class="hidden">
                @csrf @method('PATCH')
            </form>
            <x-ui.action-dropdown-item icon="power" label="{{ $aksiStatus ? 'Nonaktifkan' : 'Aktifkan' }}" variant="{{ $aksiStatus ? 'warning' : 'success' }}" onclick="document.getElementById('toggle-menu-{{ $aksiId }}').submit()" />
            @if(!$aksiUsed)
                <form id="delete-menu-{{ $aksiId }}" action="{{ route('menu.destroy', $aksiId) }}" method="POST" onsubmit="return confirmHapusMenu(event, '{{ $aksiNama }}')" class="hidden">
                    @csrf @method('DELETE')
                </form>
                <x-ui.action-dropdown-item icon="trash" label="Hapus" variant="danger" onclick="document.getElementById('delete-menu-{{ $aksiId }}').submit()" />
            @endif
        @endif
    </x-ui.action-dropdown>
</div>
@endif
