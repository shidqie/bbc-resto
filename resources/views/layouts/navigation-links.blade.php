@php
    $role = strtolower(auth()->user()?->peran?->nama_peran ?? 'pemilik');
    $isMobile = $mobile ?? false;

    // Helper function for active state
    $isActive = function ($route) {
        return request()->routeIs($route);
    };

    // Styling classes based on mobile or desktop
    $baseClass = $isMobile 
        ? "flex flex-col items-center gap-0.5 text-sm px-2 transition-colors " 
        : "flex items-center gap-3 px-3 py-2.5 rounded-xl text-base font-semibold transition-colors ";
    
    $activeClass = $isMobile 
        ? "text-brand-primary" 
        : "bg-[#F3EBDC] text-brand-primary";
    
    $inactiveClass = $isMobile 
        ? "text-[#8a8070] hover:text-brand-primary" 
        : "text-brand-text hover:bg-white/50";

    $iconClass = $isMobile ? "text-xl mb-0.5" : "text-xl";
@endphp

<!-- Dashboard (Semua Role) -->
<a href="{{ route('dashboard') }}" class="{{ $baseClass }} {{ $isActive('dashboard') ? $activeClass : $inactiveClass }}">
    <x-heroicon-o-home class="{{ $iconClass }} w-5 h-5" />
    <span>Dashboard</span>
</a>

@if(in_array($role, ['pemilik', 'admin', 'manajer']))
<!-- Menu (Pemilik / Manajer) -->
<a href="{{ route('menu.index') }}" class="{{ $baseClass }} {{ $isActive('menu.*') || $isActive('kategori-menu.*') ? $activeClass : $inactiveClass }}">
    <x-heroicon-o-sparkles class="{{ $iconClass }} w-5 h-5" />
    <span>Menu</span>
</a>
@endif

@if(in_array($role, ['pemilik', 'admin', 'kasir']))
<!-- Meja / POS (Kasir / Pemilik) -->
<a href="{{ route('pos.dinein.index') }}" class="{{ $baseClass }} {{ $isActive('pos.*') ? $activeClass : $inactiveClass }}">
    <x-heroicon-o-users class="{{ $iconClass }} w-5 h-5" />
    <span>Meja</span>
</a>
@endif

@if(in_array($role, ['pemilik', 'admin', 'dapur', 'manajer']))
<!-- Pesanan Dapur -->
<a href="{{ route('pesanan.index') }}" class="{{ $baseClass }} {{ $isActive('pesanan.*') ? $activeClass : $inactiveClass }}">
    <x-heroicon-o-clipboard-document-list class="{{ $iconClass }} w-5 h-5" />
    <span>Pesanan</span>
</a>
@endif

@if(in_array($role, ['pemilik', 'admin']))
<!-- Pengguna -->
<a href="{{ route('users.index') }}" class="{{ $baseClass }} {{ $isActive('users.*') ? $activeClass : $inactiveClass }}">
    <x-heroicon-o-users class="{{ $iconClass }} w-5 h-5" />
    <span>Pengguna</span>
</a>
@endif
