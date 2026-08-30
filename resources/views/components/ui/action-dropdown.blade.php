{{--
|--------------------------------------------------------------------------
| Action Dropdown Component
|--------------------------------------------------------------------------
| Komponen dropdown "Lainnya" untuk mengelompokkan aksi tabel jika > 2 tombol.
| Menggunakan fixed positioning agar tidak terpotong oleh overflow-x pada tabel.
|
| Props:
|   - label (string) : Label tombol (default: 'Lainnya')
|
| Contoh Pemakaian:
|   <x-ui.action-dropdown>
|       <x-ui.action-dropdown-item icon="pencil-square" label="Ubah" onclick="editModal()" />
|       <x-ui.action-dropdown-item icon="trash" label="Hapus" variant="danger" onclick="hapusModal()" />
|   </x-ui.action-dropdown>
--}}

@props([
    'label' => 'Lainnya',
])

<div x-data="{
    open: false,
    topPos: 0,
    leftPos: 0,
    toggle(btn) {
        if (this.open) {
            this.open = false;
            return;
        }
        const rect = btn.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const menuHeight = 160;
        
        if (spaceBelow < menuHeight && rect.top > menuHeight) {
            this.topPos = rect.top - menuHeight - 4;
        } else {
            this.topPos = rect.bottom + 4;
        }
        
        const menuWidth = 140;
        this.leftPos = Math.max(10, Math.min(window.innerWidth - menuWidth - 10, rect.right - menuWidth));
        this.open = true;
    }
}" 
@click.outside="open = false" 
@scroll.window="open = false"
@resize.window="open = false"
class="relative inline-block text-left">
    {{-- Trigger Button --}}
    <button type="button" 
            @click="toggle($el)"
            class="group inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 hover:border-gray-300 hover:shadow-xs active:scale-[0.98] transition-all duration-150 cursor-pointer select-none"
            aria-haspopup="true"
            :aria-expanded="open">
        <span>{{ $label }}</span>
        <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-gray-500 group-hover:text-gray-700 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
    </button>

    {{-- Dropdown Menu --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         :style="`top: ${topPos}px; left: ${leftPos}px;`"
         class="fixed z-[9999] min-w-[130px] bg-white border border-gray-200 shadow-xl rounded-xl py-1 text-xs ring-1 ring-black/5"
         style="display: none;">
        {{ $slot }}
    </div>
</div>
