{{--
|--------------------------------------------------------------------------
| Sidebar Link (Single Item)
|--------------------------------------------------------------------------
| Partial untuk menampilkan satu link navigasi di sidebar.
|
| Variabel:
|   - $route  (string) : Nama route Laravel
|   - $icon   (string) : FontAwesome icon class (contoh: "fa-gauge-high")
|   - $label  (string) : Teks label
|   - $active (bool)   : Apakah link ini sedang aktif
--}}

<a href="{{ route($route) }}" 
   class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm transition group {{ $active ? 'bg-[#0F2E23] text-white font-extrabold shadow-sm border border-emerald-800/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/50 font-medium' }}" 
   x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
    <i class="fa-solid {{ $icon }} w-6 text-center text-[16px] shrink-0 {{ $active ? 'text-emerald-400' : 'text-gray-400 group-hover:text-white' }}"></i>
    <span x-show="sidebarOpen" class="whitespace-nowrap transition-opacity duration-200">{{ $label }}</span>
</a>
