@props(['title' => 'Catatan'])

<div {{ $attributes->merge(['class' => 'bg-slate-50 border border-slate-100 rounded-xl p-4']) }}>
    @if($title)
        <h4 class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">{{ $title }}</h4>
    @endif
    <div class="text-[13px] text-slate-600 leading-relaxed">
        {{ $slot }}
    </div>
</div>
