{{--
|--------------------------------------------------------------------------
| Sidebar Link (Single Item)
|--------------------------------------------------------------------------
| Partial untuk menampilkan satu link navigasi di sidebar.
|
| Variabel:
|   - $route  (string) : Nama route Laravel
|   - $icon   (string) : Nama Heroicon (contoh: "o-squares-2x2")
|   - $label  (string) : Teks label
|   - $active (bool)   : Apakah link ini sedang aktif
--}}

<a href="{{ route($route) }}" 
   class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm transition group {{ $active ? 'bg-gray-800 text-white' : 'hover:text-white hover:bg-gray-800/50' }}" 
   title="{{ $label }}">
    @svg('heroicon-' . $icon, 'w-6 h-6 shrink-0 ' . ($active ? 'text-white' : 'text-gray-400 group-hover:text-white'))
    <span x-show="sidebarOpen" class="whitespace-nowrap transition-opacity duration-200">{{ $label }}</span>
</a>
