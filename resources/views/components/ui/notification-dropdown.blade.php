@props([
    'type' => 'internal' // 'internal' for admin/staff, 'external' for customer
])

<div x-data="{
        showNotifications: false,
        notifications: [],
        unreadCount: 0,
        loading: false,
        initialized: false,
        
        fetchNotifications() {
            this.loading = true;
            fetch('/notifikasi/unread?type={{ $type }}')
                .then(res => res.json())
                .then(data => {
                    if (this.initialized && data.unread_count > this.unreadCount) {
                        if (typeof window.showToast === 'function') {
                            const newNotif = data.notifications[0];
                            if (newNotif) {
                                window.showToast('info', newNotif.data.title + ' - ' + newNotif.data.message);
                            } else {
                                window.showToast('info', 'Ada notifikasi pesanan baru!');
                            }
                        }
                    }
                    
                    this.notifications = data.notifications;
                    this.unreadCount = data.unread_count;
                    this.loading = false;
                    this.initialized = true;
                });
        },
        
        markAsRead(id) {
            fetch(`/notifikasi/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(() => {
                this.fetchNotifications();
            });
        },
        
        markAllAsRead() {
            fetch(`/notifikasi/read-all`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(() => {
                this.fetchNotifications();
            });
        }
    }" 
    x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 10000)" 
    class="relative">
    
    <button @click="showNotifications = !showNotifications; if(showNotifications && unreadCount > 0) fetchNotifications()"
        class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
        <x-heroicon-o-bell class="w-6 h-6" />
        <span x-show="unreadCount > 0" x-text="unreadCount"
            class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
        </span>
    </button>

    <div x-show="showNotifications" @click.away="showNotifications = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 mt-2 w-80 lg:w-96 rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50 overflow-hidden"
        x-cloak>
        
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-sm font-semibold text-gray-800">Notifikasi</h3>
            <button x-show="unreadCount > 0" @click="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">Tandai semua dibaca</button>
        </div>
        
        <div class="max-h-[400px] overflow-y-auto no-scrollbar relative bg-white">
            <div x-show="loading && notifications.length === 0" class="p-4 text-center">
                <svg class="animate-spin h-5 w-5 text-gray-400 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            
            <div x-show="!loading && notifications.length === 0" class="p-6 text-center text-gray-500 flex flex-col items-center">
                <x-heroicon-o-bell-slash class="w-10 h-10 text-gray-300 mb-2" />
                <p class="text-sm">Belum ada notifikasi baru</p>
            </div>
            
            <template x-for="notif in notifications" :key="notif.id">
                <div @click="markAsRead(notif.id); if(notif.data.url) window.location.href = notif.data.url;"
                    class="px-4 py-3 border-b border-gray-50 hover:bg-slate-50 transition-colors cursor-pointer flex gap-3 relative group"
                    :class="{'bg-blue-50/30': !notif.read_at}">
                    <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0" x-show="!notif.read_at"></div>
                    <div class="w-2 h-2 shrink-0 mt-1.5" x-show="notif.read_at"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 mb-0.5" x-text="notif.data.title"></p>
                        <p class="text-xs text-gray-500 leading-snug line-clamp-2" x-text="notif.data.message"></p>
                        <p class="text-[10px] text-gray-400 mt-1.5" x-text="notif.created_at_human"></p>
                    </div>
                </div>
            </template>
        </div>
        
        <div class="px-4 py-2 border-t border-gray-100 text-center bg-gray-50/50">
            <a href="{{ $type === 'internal' ? route('admin.notifikasi.index') : route('pelanggan.notifikasi.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 transition-colors">Lihat Semua Notifikasi</a>
        </div>
    </div>
</div>
