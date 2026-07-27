{{--
|--------------------------------------------------------------------------
| Page Header Component
|--------------------------------------------------------------------------
| Menampilkan judul halaman, breadcrumbs, dan tombol aksi spesifik halaman.
--}}

@props([
    'title'       => '',
    'subtitle'    => '',
    'breadcrumbs' => [],
])

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        {{-- Breadcrumb --}}
        @if(count($breadcrumbs) > 0)
            <nav class="flex text-gray-500 text-xs mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1">
                    @foreach($breadcrumbs as $i => $crumb)
                        @if($i > 0)
                            <li><span class="mx-1.5 text-gray-300">/</span></li>
                        @endif
                        @if($i === count($breadcrumbs) - 1)
                            {{-- Item terakhir = halaman aktif --}}
                            <li class="text-gray-900 font-bold">{{ $crumb }}</li>
                        @else
                            <li class="inline-flex items-center">{{ $crumb }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        {{-- Judul --}}
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $title }}</h1>

        {{-- Subtitle --}}
        @if($subtitle)
            <p class="text-[13px] text-gray-500 mt-1 font-medium">{{ $subtitle }}</p>
        @endif
    </div>

    {{-- Tombol Aksi Halaman (kanan) --}}
    @if(isset($actions))
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
