{{--
|--------------------------------------------------------------------------
| Sidebar Link (Single Item)
|--------------------------------------------------------------------------
| Partial untuk menampilkan satu link navigasi di sidebar.
|
| Variabel:
|   - $route  (string) : Nama route Laravel
|   - $icon   (string) : Heroicon name (contoh: "home", "chart-pie")
|   - $label  (string) : Teks label
|   - $active (bool)   : Apakah link ini sedang aktif
--}}

<a href="{{ route($route) }}" 
   class="flex items-center gap-3 px-3 py-3 rounded-lg text-[15px] transition-all duration-200 group relative overflow-hidden
          {{ $active 
              ? 'bg-white/10 text-white font-semibold shadow-sm' 
              : 'text-gray-300 hover:text-white hover:bg-white/5 font-medium' }}" 
   x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
    {{-- Active left-border indicator --}}
    @if($active)
    <span class="absolute left-0 top-1.5 bottom-1.5 w-[3px] bg-white rounded-r-full"></span>
    @endif
    <x-dynamic-component :component="$icon" class="w-6 h-6 shrink-0 transition-colors duration-200 {{ $active ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" />
    <span x-show="sidebarOpen" class="whitespace-nowrap leading-none truncate flex-1 text-left">{{ $label }}</span>
</a>
