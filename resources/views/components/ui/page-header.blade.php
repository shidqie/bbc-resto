{{--
|--------------------------------------------------------------------------
| Page Header Component
|--------------------------------------------------------------------------
| Header halaman: breadcrumb + judul + subtitle + tombol aksi (slot).
--}}

@props([
    'title'       => '',
    'subtitle'    => '',
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @if(count($breadcrumbs) > 0)
        <nav class="flex text-sm text-gray-400 mb-1" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1">
                @foreach($breadcrumbs as $i => $crumb)
                    @if($i > 0)
                        <li><span class="mx-1.5 text-gray-300">/</span></li>
                    @endif
                    <li class="{{ $i === count($breadcrumbs) - 1 ? 'text-gray-700 font-medium' : '' }}">{{ $crumb }}</li>
                @endforeach
            </ol>
        </nav>
    @endif

    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-primary tracking-tight">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>

        @if(isset($actions))
            <div class="flex items-center gap-2 shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
