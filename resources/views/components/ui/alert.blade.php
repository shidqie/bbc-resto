{{--
|--------------------------------------------------------------------------
| Alert Component
|--------------------------------------------------------------------------
| Menampilkan pesan flash session secara otomatis (success, error, warning).
--}}

{{-- Alert Sukses --}}
@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center gap-3">
        <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
@endif

{{-- Alert Error --}}
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center gap-3">
        <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0" />
        <p class="text-sm font-medium">{{ session('error') }}</p>
    </div>
@endif

{{-- Alert Warning --}}
@if(session('warning') || session('warning_bom'))
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg flex items-start gap-3">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0 mt-0.5" />
        <p class="text-sm font-medium">{{ session('warning') ?? session('warning_bom') }}</p>
    </div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex flex-col gap-1">
        @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <x-heroicon-o-exclamation-circle class="w-4 h-4 shrink-0" />
                <p class="text-sm font-medium">{{ $error }}</p>
            </div>
        @endforeach
    </div>
@endif
