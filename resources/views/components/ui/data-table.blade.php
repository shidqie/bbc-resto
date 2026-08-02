{{--
|--------------------------------------------------------------------------
| Data Table Component
|--------------------------------------------------------------------------
| Komponen wrapper untuk tabel data. Sudah termasuk:
| - Card container dengan rounded corners
| - Slot untuk toolbar/filter di atas tabel
| - Area tabel yang scrollable
| - Pagination di bagian bawah
|
| Props:
|   - paginator (object) : Objek paginator Laravel (opsional, untuk pagination)
|
| Slots:
|   - toolbar : Area filter/search di atas tabel
|   - default : Isi tabel (thead + tbody)
|
| Contoh Pemakaian:
|   <x-ui.data-table :paginator="$bahanBakus">
|       <x-slot:toolbar>
|           <form>... search & filters ...</form>
|       </x-slot:toolbar>
|
|       <table>
|           <thead>...</thead>
|           <tbody>...</tbody>
|       </table>
|   </x-ui.data-table>
--}}

@props([
    'paginator' => null,
])

<div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
    {{-- Toolbar (Search, Filter, dll) --}}
    @if(isset($toolbar))
        <div class="p-3.5 border-b border-gray-100 bg-white">
            {{ $toolbar }}
        </div>
    @endif

    {{-- Tabel (scrollable) --}}
    <div class="flex-1 overflow-x-auto">
        {{ $slot }}
    </div>

    {{-- Pagination --}}
    @if($paginator && $paginator->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 bg-white">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
