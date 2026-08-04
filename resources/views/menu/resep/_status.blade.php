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
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-green-50 text-green-700" title="{{ $count }} bahan baku">
        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $count }} Bahan
    </span>
@elseif($count > 0)
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-amber-50 text-amber-700" title="{{ $count }} bahan, belum dikonfirmasi">
        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Resep Belum Lengkap
    </span>
@else
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-rose-50 text-rose-400">
        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Belum Ada Resep
    </span>
@endif
