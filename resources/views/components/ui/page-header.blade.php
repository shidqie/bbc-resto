{{--
|--------------------------------------------------------------------------
| Page Header Component (Minimalist)
|--------------------------------------------------------------------------
| Header halaman ringkas: judul bold + subtitle + tombol aksi (opsional).
| Breadcrumb dihilangkan demi konsistensi minimalis di seluruh halaman.
| Props: title, subtitle, breadcrumbs (opsional, tidak dirender), actions
|--------------------------------------------------------------------------
--}}

@props([
    'title'       => '',
    'subtitle'    => '',
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'flex items-center justify-between gap-3']) }}>
    <div>
        @if(count($breadcrumbs) > 0)
            <nav class="flex text-gray-500 text-sm mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1">
                    @foreach($breadcrumbs as $i => $crumb)
                        @if($i > 0)
                            <li><span class="mx-1.5 text-gray-300">/</span></li>
                        @endif
                        <li class="{{ $i === count($breadcrumbs) - 1 ? 'text-gray-900 font-semibold' : '' }}">{{ $crumb }}</li>
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1 class="text-lg font-bold text-gray-900 tracking-tight">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
