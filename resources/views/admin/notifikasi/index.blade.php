@extends('layouts.pos')

@section('title', 'Notifikasi Sistem')

@section('content')
<div class="flex-1 flex flex-col h-full overflow-hidden bg-[#F8FAFC]">
    
    {{-- Header --}}
    <header class="h-16 bg-white border-b border-gray-200 px-6 flex items-center justify-between shrink-0 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-lg">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Notifikasi Masuk</h1>
                <p class="text-xs text-gray-500">Pemberitahuan pesanan baru, bukti bayar, dan pelunasan.</p>
            </div>
        </div>

        <form action="{{ route('admin.notifikasi.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors flex items-center gap-2">
                <i class="fa-solid fa-check-double text-green-600"></i> Tandai Semua Dibaca
            </button>
        </form>
    </header>

    {{-- Content Area --}}
    <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-4xl mx-auto space-y-4">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-semibold flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-circle-check text-green-600"></i> {{ session('success') }}
                </div>
            @endif

            @forelse($notifikasis as $notif)
                @php
                    $bgStyle = $notif->is_read ? 'bg-white border-gray-200 opacity-80' : 'bg-white border-l-4 border-l-primary border-gray-200 shadow-sm';
                    $iconStyle = 'bg-blue-100 text-blue-600';
                    $iconClass = 'fa-basket-shopping';

                    if ($notif->tipe === 'pelunasan') {
                        $iconStyle = 'bg-emerald-100 text-emerald-600';
                        $iconClass = 'fa-file-circle-check';
                    } elseif ($notif->tipe === 'bukti_pembayaran') {
                        $iconStyle = 'bg-amber-100 text-amber-600';
                        $iconClass = 'fa-receipt';
                    }
                @endphp

                <div class="p-5 border rounded-2xl transition-all duration-200 hover:shadow-md {{ $bgStyle }}">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl {{ $iconStyle }} flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid {{ $iconClass }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h3 class="font-bold text-gray-900 text-base truncate">{{ $notif->judul }}</h3>
                                <span class="text-xs text-gray-400 font-medium shrink-0">
                                    {{ $notif->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed mb-3">{{ $notif->pesan }}</p>
                            
                            @if($notif->link)
                                <a href="{{ url($notif->link) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary hover:bg-primary hover:text-white text-xs font-bold rounded-lg transition-colors">
                                    Buka Detail <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                    <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-bell-slash"></i>
                    </div>
                    <h3 class="font-bold text-gray-700 text-base mb-1">Belum Ada Notifikasi</h3>
                    <p class="text-xs text-gray-400">Pemberitahuan transaksi baru akan muncul di sini.</p>
                </div>
            @endforelse

            <div class="mt-6">
                {{ $notifikasis->links() }}
            </div>

        </div>
    </main>
</div>
@endsection
