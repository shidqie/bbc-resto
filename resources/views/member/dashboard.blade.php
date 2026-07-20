@extends('layouts.guest')

@section('title', 'Dasbor Konsumen')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <div class="flex items-center justify-between bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Halo, {{ Auth::user()->name }}!</h1>
                <p class="text-gray-600">Selamat datang di Dasbor Konsumen. Di sini Anda bisa memantau riwayat pesanan Anda.</p>
            </div>
            <div>
                <a href="{{ route('home') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 text-sm font-semibold transition-colors">
                    Pesan Makanan Lagi
                </a>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            {{-- Pesanan Nasi Box --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-4 flex items-center justify-between">
                        <span><i class="fa-solid fa-box mr-2 text-primary"></i>Riwayat Nasi Box</span>
                        <span class="text-sm bg-blue-100 text-blue-800 py-1 px-2 rounded-full">{{ $nasiboxOrders->count() }}</span>
                    </h2>
                    
                    @if($nasiboxOrders->isEmpty())
                        <p class="text-gray-500 text-sm text-center py-4">Belum ada pesanan Nasi Box.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($nasiboxOrders as $order)
                                <a href="{{ route('pesanan.status', $order->kode_pesanan) }}" class="block border rounded-lg p-4 hover:border-primary hover:shadow-sm transition-all">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-bold text-primary">#{{ $order->kode_pesanan }}</span>
                                        <span class="text-xs px-2 py-1 rounded-full font-medium {{ $order->status === 'menunggu_dp' || $order->status === 'menunggu_konfirmasi' ? 'bg-yellow-100 text-yellow-800' : ($order->status === 'selesai' || $order->status === 'dikirim' || $order->status === 'lunas' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ str_replace('_', ' ', strtoupper($order->status)) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-1">{{ $order->tanggal_acara->format('d M Y') }}</div>
                                    <div class="text-sm font-semibold">Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}</div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pesanan Catering --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-4 flex items-center justify-between">
                        <span><i class="fa-solid fa-utensils mr-2 text-primary"></i>Riwayat Catering</span>
                        <span class="text-sm bg-blue-100 text-blue-800 py-1 px-2 rounded-full">{{ $cateringOrders->count() }}</span>
                    </h2>
                    
                    @if($cateringOrders->isEmpty())
                        <p class="text-gray-500 text-sm text-center py-4">Belum ada pesanan Catering.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($cateringOrders as $order)
                                <a href="{{ route('pesanan.status', $order->kode_pesanan) }}" class="block border rounded-lg p-4 hover:border-primary hover:shadow-sm transition-all">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-bold text-primary">#{{ $order->kode_pesanan }}</span>
                                        <span class="text-xs px-2 py-1 rounded-full font-medium {{ $order->status === 'menunggu_dp' || $order->status === 'menunggu_konfirmasi' ? 'bg-yellow-100 text-yellow-800' : ($order->status === 'selesai' || $order->status === 'dikirim' || $order->status === 'lunas' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ str_replace('_', ' ', strtoupper($order->status)) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-1">{{ $order->tanggal_acara->format('d M Y') }}</div>
                                    <div class="text-sm font-semibold">Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}</div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
