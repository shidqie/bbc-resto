{{--
|--------------------------------------------------------------------------
| Form Error Component
|--------------------------------------------------------------------------
| Menampilkan pesan error validasi (bawaan $errors Laravel) secara konsisten.
|
| Props:
|   - field   (string) : Nama field. Jika diisi, hanya error field tsb.
|                        Jika kosong, semua error ($errors->all()) ditampilkan.
|   - class   (string) : Kelas tambahan
|
| Contoh:
|   <x-form-error field="nama" />        // Error khusus field "nama"
|   <x-form-error />                     // Semua error form
|--------------------------------------------------------------------------------
--}}

@props([
    'field' => '',
    'class' => '',
])

@if($field)
    {{-- Error per field --}}
    @error($field)
        <p class="mt-1 text-xs font-medium text-red-500 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $message }}
        </p>
    @enderror
@elseif($errors->any())
    {{-- Semua error --}}
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 {{ $class }}" role="alert">
        <div class="flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="font-semibold">Terjadi kesalahan pada pengisian form:</p>
                <ul class="mt-1.5 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif