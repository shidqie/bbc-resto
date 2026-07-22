@php
    $role = auth()->user()?->role?->name ?? 'Pemilik'; // Default placeholder
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
    <span class="{{ $iconClass }}">⌂</span>
    <span>Dashboard</span>
</a>

@if(in_array($role, ['pemilik']))
<!-- Menu (Pemilik) -->
<a href="{{ route('menus.index') }}" class="{{ $baseClass }} {{ $isActive('menus.*') || $isActive('kategori.*') ? $activeClass : $inactiveClass }}">
    <span class="{{ $iconClass }}">🍽</span>
    <span>Menu</span>
</a>
@endif

@if(in_array($role, ['pemilik', 'kasir']))
<!-- Meja / POS (Kasir / Pemilik) -->
<a href="#" class="{{ $baseClass }} {{ $isActive('pos.*') ? $activeClass : $inactiveClass }}">
    <span class="{{ $iconClass }}">🪑</span>
    <span>Meja</span>
</a>
@endif

@if(in_array($role, ['pemilik', 'dapur']))
<!-- Pesanan Dapur (Dapur / Pemilik) -->
<a href="#" class="{{ $baseClass }} {{ $isActive('dapur.*') ? $activeClass : $inactiveClass }}">
    <span class="{{ $iconClass }}">🔥</span>
    <span>Pesanan</span>
</a>
@endif

@if(in_array($role, ['pemilik']))
<!-- Jadwal Catering/Box (Pemilik) -->
<a href="#" class="{{ $baseClass }} {{ $isActive('jadwal.*') ? $activeClass : $inactiveClass }}">
    <span class="{{ $iconClass }}">📅</span>
    <span>Jadwal</span>
</a>

<!-- Pengguna (Pemilik) -->
<a href="{{ route('users.index') }}" class="{{ $baseClass }} {{ $isActive('users.*') ? $activeClass : $inactiveClass }}">
    <span class="{{ $iconClass }}">👥</span>
    <span>Pengguna</span>
</a>

<!-- Lainnya (Pemilik) -->
<a href="#" class="{{ $baseClass }} {{ $isActive('lainnya.*') ? $activeClass : $inactiveClass }}">
    <span class="{{ $iconClass }}">⋯</span>
    <span>Lainnya</span>
</a>
@endif
