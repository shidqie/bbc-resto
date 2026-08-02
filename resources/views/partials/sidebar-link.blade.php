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
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors duration-200 group relative overflow-hidden
          {{ $active
              ? 'bg-neutral-100 text-neutral-900 font-medium'
              : 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-50' }}"
   x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
    @if($active)
    <span class="absolute left-0 top-1.5 bottom-1.5 w-[2.5px] bg-neutral-900"></span>
    @endif
    <x-dynamic-component :component="$icon" class="w-5 h-5 shrink-0 transition-colors duration-200 {{ $active ? 'text-neutral-900' : 'text-neutral-400 group-hover:text-neutral-900' }}" />
    <span x-show="sidebarOpen" class="whitespace-nowrap leading-none truncate flex-1 text-left">{{ $label }}</span>
</a>
