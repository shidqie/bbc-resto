@php
    $role = strtolower(auth()->user()?->role?->name ?? 'pemilik');
    $isMobile = $mobile ?? false;

    // Helper function for active state
    $isActive = function ($route) {
        return request()->routeIs($route);
    };

    // Styling classes based on mobile or desktop
    $baseClass = $isMobile 
        ? "flex flex-col items-center gap-0.5 text-[10px] px-2 transition-colors " 
        : "flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors ";
    
    $activeClass = $isMobile 
        ? "text-brand-primary" 
        : "bg-[#F3EBDC] text-brand-primary";
    
    $inactiveClass = $isMobile 
        ? "text-[#8a8070] hover:text-brand-primary" 
        : "text-brand-text hover:bg-white/50";

    $iconClass = $isMobile ? "text-lg mb-0.5" : "text-lg";
@endphp

<!-- Dashboard (Semua Role) -->
<a href="{{ route('dashboard') }}" class="{{ $baseClass }} {{ $isActive('dashboard') ? $activeClass : $inactiveClass }}">
    <i class="fa-solid fa-house {{ $iconClass }}"></i>
    <span>Dashboard</span>
</a>

@if(in_array($role, ['pemilik', 'admin', 'manajer']))
<!-- Menu (Pemilik / Manajer) -->
<a href="{{ route('menu.index') }}" class="{{ $baseClass }} {{ $isActive('menu.*') || $isActive('kategori-menu.*') ? $activeClass : $inactiveClass }}">
    <i class="fa-solid fa-utensils {{ $iconClass }}"></i>
    <span>Menu</span>
</a>
@endif

@if(in_array($role, ['pemilik', 'admin', 'kasir']))
<!-- Meja / POS (Kasir / Pemilik) -->
<a href="{{ route('pos.dinein.index') }}" class="{{ $baseClass }} {{ $isActive('pos.*') ? $activeClass : $inactiveClass }}">
    <i class="fa-solid fa-chair {{ $iconClass }}"></i>
    <span>Meja</span>
</a>
@endif

@if(in_array($role, ['pemilik', 'admin', 'dapur', 'manajer']))
<!-- Pesanan Dapur -->
<a href="{{ route('pesanan.index') }}" class="{{ $baseClass }} {{ $isActive('pesanan.*') ? $activeClass : $inactiveClass }}">
    <i class="fa-solid fa-clipboard-list {{ $iconClass }}"></i>
    <span>Pesanan</span>
</a>
@endif

@if(in_array($role, ['pemilik', 'admin']))
<!-- Pengguna -->
<a href="{{ route('users.index') }}" class="{{ $baseClass }} {{ $isActive('users.*') ? $activeClass : $inactiveClass }}">
    <i class="fa-solid fa-users {{ $iconClass }}"></i>
    <span>Pengguna</span>
</a>
@endif
