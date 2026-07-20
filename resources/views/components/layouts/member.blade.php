<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Portal Member' }} - Saung Babakan Cinta</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #FAFAF7; color: #3D3D3D; }
        h1, h2, h3, h4, h5, h6, .font-serif { font-family: 'Outfit', sans-serif; color: #0D3024; }
        .bg-batik {
            background-image: url('data:image/svg+xml;utf8,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M20 0l20 20-20 20L0 20z" fill="%230D3024" fill-opacity="0.03" fill-rule="evenodd"/></svg>');
        }
        .mobile-nav { display: none; }
        .mobile-nav.active { display: flex; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col relative bg-gray-50">

    {{-- Gunakan Navbar Publik (landing page) --}}
    <x-landing.navbar />

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 mt-24">
        
        <div class="flex flex-col md:flex-row gap-8">
            {{-- Sidebar Navigasi Member --}}
            <aside class="w-full md:w-64 shrink-0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-28">
                    <div class="p-6 border-b border-gray-100 bg-batik">
                        <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <h3 class="text-center font-semibold text-lg">{{ auth()->user()->name }}</h3>
                        <p class="text-center text-sm text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    
                    <nav class="p-4 space-y-4">
                        
                        {{-- Kategori Akun Saya --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-4">Akun Saya</h4>
                            <div class="space-y-0.5">
                                <a href="{{ route('member.profile') }}" class="flex items-center gap-3 px-4 py-2 rounded-xl transition-colors {{ request()->routeIs('member.profile') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <i class="fa-solid fa-user w-5 text-center"></i> Profil
                                </a>
                                <a href="{{ route('member.alamat') }}" class="flex items-center gap-3 px-4 py-2 rounded-xl transition-colors {{ request()->routeIs('member.alamat') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <i class="fa-solid fa-location-dot w-5 text-center"></i> Buku Alamat
                                </a>
                                <a href="{{ route('member.password') }}" class="flex items-center gap-3 px-4 py-2 rounded-xl transition-colors {{ request()->routeIs('member.password') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <i class="fa-solid fa-lock w-5 text-center"></i> Ubah Password
                                </a>
                            </div>
                        </div>

                        {{-- Kategori Pesanan Saya --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-4">Pesanan Saya</h4>
                            <div class="space-y-0.5">
                                <a href="{{ route('member.pesanan.aktif') }}" class="flex items-center gap-3 px-4 py-2 rounded-xl transition-colors {{ request()->routeIs('member.pesanan.aktif') || request()->routeIs('member.dashboard') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <i class="fa-solid fa-box-open w-5 text-center"></i> Pesanan Aktif
                                </a>
                                <a href="{{ route('member.pesanan.riwayat') }}" class="flex items-center gap-3 px-4 py-2 rounded-xl transition-colors {{ request()->routeIs('member.pesanan.riwayat') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i> Riwayat Pesanan
                                </a>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-100"></div>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-danger hover:bg-red-50 transition-colors">
                                <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> Keluar
                            </button>
                        </form>
                    </nav>
                </div>
            </aside>

            {{-- Area Konten Utama --}}
            <section class="flex-1">
                {{ $slot }}
            </section>
        </div>

    </main>

    {{-- Gunakan Footer Publik --}}
    <x-landing.footer />

    @stack('scripts')
</body>
</html>
