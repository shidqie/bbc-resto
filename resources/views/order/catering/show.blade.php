@extends('layouts.pos')

@section('title', 'Detail Pesanan Katering #' . $pesanan->id_pesanan)

@section('content')
<div class="w-full p-6 max-w-[1200px] mx-auto">
    <div class="w-full p-6 flex justify-between items-center mb-6">
        <x-ui.page-header title="Detail Pesanan Katering #{{ $pesanan->id_pesanan }}" subtitle="Rincian lengkap pesanan katering, item menu, pembayaran & status." :breadcrumbs="['Penjualan', 'Katering', 'Detail']">
            <x-slot:actions>
                <div class="flex gap-2">
                    <a href="{{ route('admin.pesanan.catering.index') }}" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition-colors">&larr; Kembali</a>
                </div>
            </x-slot:actions>
        </x-ui.page-header>
    </div>

    @include('order.catering._detail')
</div>
@endsection
