<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Menu Digital - SBC Resto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#3B82F6',
                            container: '#DBEAFE',
                        },
                        secondary: {
                            DEFAULT: '#8B5CF6',
                        },
                        success: {
                            DEFAULT: '#16A34A',
                        },
                        danger: {
                            DEFAULT: '#DC2626',
                        },
                        warning: {
                            DEFAULT: '#D97706',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Hide scrollbar for category menu but allow scrolling */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased selection:bg-primary selection:text-white" x-data="{ activeCategory: 'all' }">

    <!-- Header -->
    <header class="bg-white sticky top-0 z-50 border-b border-gray-100 shadow-sm">
        <div class="px-5 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-gray-900">SBC Resto</h1>
                <p class="text-xs font-medium text-gray-500 mt-0.5">Silakan pesan melalui staf kami</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <i class="fa-solid fa-utensils"></i>
            </div>
        </div>

        <!-- Categories Scroll -->
        <div class="px-5 py-3 overflow-x-auto no-scrollbar flex items-center gap-2 border-t border-gray-50">
            <button @click="activeCategory = 'all'" 
                    :class="activeCategory === 'all' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition-all duration-300">
                Semua Menu
            </button>
            @foreach($kategoris as $k)
                <button @click="activeCategory = '{{ $k->id }}'" 
                        :class="activeCategory === '{{ $k->id }}' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                        class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition-all duration-300">
                    {{ $k->nama_kategori }}
                </button>
            @endforeach
        </div>
    </header>

    <!-- Menu List -->
    <main class="p-5 pb-24">
        
        <!-- Info Banner -->
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6 flex items-start gap-3 shadow-sm">
            <div class="text-primary mt-0.5">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-blue-900">Cara Memesan</h3>
                <p class="text-xs text-blue-800 mt-1 leading-relaxed">
                    Lihat menu yang Anda inginkan di bawah, lalu <strong>panggil pelayan kami</strong> untuk mencatat pesanan Anda. Pembayaran dilakukan di kasir setelah selesai bersantap.
                </p>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-2 gap-4">
            @forelse($menus as $menu)
                <div x-show="activeCategory === 'all' || activeCategory === '{{ $menu->kategori_id }}'" 
                     x-transition.opacity
                     class="bg-white rounded-[20px] overflow-hidden shadow-sm border border-gray-100 flex flex-col group hover:shadow-md transition-shadow">
                    
                    <!-- Image -->
                    <div class="relative w-full aspect-square bg-gray-100">
                        @if($menu->gambar)
                            <img src="{{ asset('storage/' . $menu->gambar) }}" alt="{{ $menu->nama_menu }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                <i class="fa-solid fa-image text-2xl mb-2"></i>
                                <span class="text-[10px] uppercase font-bold tracking-wider">No Image</span>
                            </div>
                        @endif
                        
                        @if($menu->kategori)
                            <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg text-[10px] font-bold text-gray-700 shadow-sm">
                                {{ $menu->kategori->nama_kategori }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="p-3 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm leading-tight line-clamp-2 mb-1">{{ $menu->nama_menu }}</h3>
                            <p class="text-[11px] text-gray-500 line-clamp-2 leading-relaxed">{{ $menu->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                        </div>
                        <div class="mt-3 font-bold text-primary text-sm flex items-end">
                            Rp {{ number_format($menu->harga, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-10 bg-white rounded-2xl border border-gray-100">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400">
                        <i class="fa-solid fa-utensils text-2xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Belum Ada Menu</h3>
                    <p class="text-xs text-gray-500 mt-1">Daftar menu sedang diperbarui.</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- Bottom Action Sheet (Optional, purely aesthetic for completeness) -->
    <div class="fixed bottom-0 left-0 right-0 p-5 bg-gradient-to-t from-gray-50 via-gray-50 to-transparent pointer-events-none flex justify-center">
        <div class="bg-gray-900 text-white px-5 py-3 rounded-full shadow-xl pointer-events-auto flex items-center gap-3 text-sm font-bold opacity-90 backdrop-blur-md hover:scale-105 transition-transform cursor-pointer" onclick="window.scrollTo(0,0)">
            <i class="fa-solid fa-arrow-up"></i>
            Kembali ke Atas
        </div>
    </div>

</body>
</html>
