@php
    $layout = request('layout') === 'b' ? 'b' : 'a';
@endphp
@include("pelanggan.pesanan.partials._summary-layout-{$layout}", ['config' => $config ?? []])
