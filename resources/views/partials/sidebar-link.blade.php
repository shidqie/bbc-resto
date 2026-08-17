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
   class="flex items-center rounded-xl text-sm transition-all duration-300 group relative
          {{ $active
              ? 'bg-blue-50 text-blue-700 font-semibold shadow-sm'
              : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100/80' }}"
   :class="sidebarOpen ? 'px-3.5 py-2.5 gap-3.5 justify-start' : 'w-10 h-10 px-0 py-0 justify-center mx-auto'"
   x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
    <x-dynamic-component :component="$icon" class="w-5 h-5 shrink-0 transition-all duration-300 {{ $active ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
    <span x-show="sidebarOpen" class="whitespace-nowrap leading-none truncate flex-1 text-left tracking-wide" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">{{ $label }}</span>
</a>
