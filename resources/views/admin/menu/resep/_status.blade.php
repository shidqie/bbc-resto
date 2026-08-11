@php
    if (!isset($menu) || !$menu) {
        $count = 0;
        $lengkap = false;
        $nama = '';
    } else {
        $count = $menu->resep_menu_count ?? $menu->resep_menu->count();
        $lengkap = $count > 0 && $menu->resep_menu->every(fn ($r) => $r->dikonfirmasi);
        $nama = $menu->nama_menu ?? '';
    }
@endphp
@if($count > 0 && $lengkap)
    <x-ui.badge color="success" size="sm" title="{{ $count }} bahan baku">✓ {{ $count }} Bahan</x-ui.badge>
@elseif($count > 0)
    <x-ui.badge color="warning" size="sm" title="{{ $count }} bahan, belum dikonfirmasi">Resep Belum Lengkap</x-ui.badge>
@else
    <x-ui.badge color="gray" size="sm">Belum Ada Resep</x-ui.badge>
@endif
