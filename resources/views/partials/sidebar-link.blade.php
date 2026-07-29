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
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] transition-all duration-200 group relative overflow-hidden
          {{ $active 
              ? 'bg-gray-800 text-white font-semibold' 
              : 'text-gray-500 hover:text-white hover:bg-gray-800/50 font-medium' }}" 
   x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
    {{-- Active left-border indicator --}}
    @if($active)
    <span class="absolute left-0 top-1.5 bottom-1.5 w-[3px] bg-white rounded-r-full"></span>
    @endif
    <i class="fa-solid {{ $icon }} w-4 text-center text-[13px] shrink-0 transition-colors duration-200
              {{ $active ? 'text-white' : 'text-gray-500 group-hover:text-white' }}"></i>
    <span x-show="sidebarOpen" class="whitespace-nowrap leading-none">{{ $label }}</span>
</a>
