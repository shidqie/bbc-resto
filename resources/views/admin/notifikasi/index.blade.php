@extends('layouts.pos')

@section('content')
    <div class="px-6 py-4 pb-10">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Semua Notifikasi</h1>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @forelse($notifications as $notif)
                <div class="p-4 border-b border-gray-100 flex items-start gap-4 {{ is_null($notif->read_at) ? 'bg-blue-50/30' : '' }}">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">{{ $notif->data['title'] ?? 'Notifikasi' }}</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ $notif->data['message'] ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-2">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                    @if($notif->data['url'] ?? false)
                        <a href="{{ $notif->data['url'] }}" class="text-sm text-blue-600 font-medium">Lihat Detail</a>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">Belum ada notifikasi.</div>
            @endforelse
        </div>
        
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection
