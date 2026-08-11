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
|--}}

<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-base transition-colors duration-200 group relative
          {{ $active
              ? 'bg-neutral-100/80 text-[#0D3024] font-semibold'
              : 'text-neutral-500 hover:text-neutral-900 hover:bg-neutral-50' }}"
   x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
    @if($active)
    <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-[#0D3024]"></span>
    @endif
    <x-dynamic-component :component="$icon" class="w-5 h-5 shrink-0 transition-colors duration-200 {{ $active ? 'text-[#0D3024]' : 'text-neutral-400 group-hover:text-neutral-600' }}" />
    <span x-show="sidebarOpen" class="whitespace-nowrap leading-none truncate flex-1 text-left">{{ $label }}</span>
</a>
