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
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-base transition-colors duration-200 group relative overflow-hidden
          {{ $active
              ? 'bg-[#0D3024] text-white font-medium shadow-sm'
              : 'text-neutral-600 hover:text-[#0D3024] hover:bg-emerald-50/70' }}"
   x-bind:title="!sidebarOpen ? '{{ $label }}' : ''">
    @if($active)
    <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r-full bg-[#D4A843]"></span>
    @endif
    <x-dynamic-component :component="$icon" class="w-5 h-5 shrink-0 transition-colors duration-200 {{ $active ? 'text-[#D4A843]' : 'text-neutral-400 group-hover:text-[#0D3024]' }}" />
    <span x-show="sidebarOpen" class="whitespace-nowrap leading-none truncate flex-1 text-left">{{ $label }}</span>
</a>
