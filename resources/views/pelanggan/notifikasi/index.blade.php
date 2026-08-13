<x-layouts.landing title="Notifikasi Saya">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-primary mb-8 font-serif">Notifikasi Saya</h1>
        
        <div class="bg-surface rounded-2xl shadow-sm border border-neutral-100 overflow-hidden">
            @forelse($notifications as $notif)
                <div class="p-5 border-b border-neutral-100 flex items-start gap-4 {{ is_null($notif->read_at) ? 'bg-primary-container' : '' }}">
                    <div class="flex-1">
                        <h4 class="font-medium text-neutral-900">{{ $notif->data['title'] ?? 'Notifikasi' }}</h4>
                        <p class="text-sm text-neutral-600 mt-1">{{ $notif->data['message'] ?? '' }}</p>
                        <p class="text-xs text-neutral-400 mt-2">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                    @if($notif->data['url'] ?? false)
                        <a href="{{ $notif->data['url'] }}" class="text-sm text-primary font-medium">Lihat Detail</a>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center text-neutral-500 flex flex-col items-center">
                    <x-heroicon-o-bell-slash class="w-12 h-12 text-neutral-300 mb-4" />
                    Belum ada notifikasi untuk Anda.
                </div>
            @endforelse
        </div>
        
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </div>
</x-layouts.landing>
