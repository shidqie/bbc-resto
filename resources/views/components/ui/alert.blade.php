{{--
|--------------------------------------------------------------------------
| Alert Component
|--------------------------------------------------------------------------
| Komponen untuk menampilkan pesan flash (session) secara otomatis.
| Cukup panggil <x-ui.alert /> maka akan otomatis tampil jika ada
| session('success') atau session('error').
|
| Props: (tidak ada — semuanya otomatis dari session)
|
| Contoh Pemakaian:
|   <x-ui.alert />
--}}

{{-- Alert Sukses --}}
@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
        <i class="fas fa-check-circle shrink-0"></i>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
@endif

{{-- Alert Error --}}
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-3">
        <i class="fas fa-exclamation-circle shrink-0"></i>
        <p class="text-sm font-medium">{{ session('error') }}</p>
    </div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex flex-col gap-1">
        @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-xs shrink-0"></i>
                <p class="text-sm font-medium">{{ $error }}</p>
            </div>
        @endforeach
    </div>
@endif
