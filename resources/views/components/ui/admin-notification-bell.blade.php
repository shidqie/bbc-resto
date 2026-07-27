@php
    $userRole = auth()->user()->role->name ?? '';
    $isPemilik = in_array($userRole, ['Pemilik', 'Admin', 'Super Admin']);

    $query = \App\Models\NotifikasiAdmin::query();

    // Jika bukan Pemilik/Admin, filter out notifikasi Catering & Nasi Box
    if (!$isPemilik) {
        $query->where(function($q) {
            $q->whereNotIn('tipe', ['catering', 'nasibox', 'nasi_box', 'pelunasan_catering'])
              ->where(function($sub) {
                  $sub->whereNull('link')
                      ->orWhere(function($l) {
                          $l->where('link', 'not like', '%catering%')
                            ->where('link', 'not like', '%nasi-box%')
                            ->where('link', 'not like', '%nasibox%');
                      });
              })
              ->where('judul', 'not like', '%catering%')
              ->where('judul', 'not like', '%nasi box%')
              ->where('judul', 'not like', '%nasibox%')
              ->where('pesan', 'not like', '%catering%')
              ->where('pesan', 'not like', '%nasi box%')
              ->where('pesan', 'not like', '%nasibox%');
        });
    }

    $unreadCount = (clone $query)->where('is_read', false)->count();
    $recentNotifs = $query->latest()->take(6)->get();
@endphp

<div class="relative inline-block z-[9999]" x-data="{ open: false }" @click.outside="open = false">
    {{-- Tombol Lonceng Notifikasi --}}
    <button @click="open = !open" type="button" 
            class="relative w-10 h-10 rounded-full bg-[#111827] hover:bg-[#0F2E23] text-white flex items-center justify-center transition-all duration-200 shadow-xs focus:outline-none group active:scale-95" 
            title="Notifikasi Masuk">
        <i class="fa-solid fa-bell text-sm group-hover:rotate-12 transition-transform duration-200"></i>
        
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex h-5 min-w-[20px] px-1 items-center justify-center rounded-full bg-red-500 text-[10px] font-black text-white ring-2 ring-white animate-pulse shadow-sm">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown Popover Notifikasi Minimalis --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="absolute right-0 mt-3 w-80 sm:w-96 rounded-2xl bg-white border border-gray-100 shadow-2xl z-[9999] overflow-hidden"
         style="display: none;">

        {{-- Header Popover Minimalis --}}
        <div class="px-4 py-3 bg-[#0F2E23] text-white flex items-center justify-between border-b border-emerald-900/50">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-bell text-amber-400 text-xs"></i>
                <h3 class="font-extrabold text-xs tracking-tight">Notifikasi Masuk</h3>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 bg-red-500 text-white text-[9px] font-black rounded-full shadow-2xs">
                        {{ $unreadCount }} Baru
                    </span>
                @endif
            </div>

            @if($unreadCount > 0)
            <form action="{{ route('admin.notifikasi.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-[11px] text-emerald-200/90 hover:text-white font-bold transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-check-double text-[10px]"></i>
                    <span>Tandai Dibaca</span>
                </button>
            </form>
            @endif
        </div>

        {{-- Item List Minimalis --}}
        <div class="divide-y divide-gray-100/80 max-h-88 overflow-y-auto">
            @forelse($recentNotifs as $notif)
                @php
                    $isCatering = Str::contains(strtolower($notif->judul . ' ' . $notif->pesan . ' ' . $notif->tipe), ['catering', 'nasi_box', 'nasibox', 'nasi box']);
                    
                    $icon = 'fa-bag-shopping';
                    $iconStyle = 'bg-blue-50 text-blue-600 border-blue-100';
                    
                    if ($notif->tipe === 'pelunasan' || Str::contains(strtolower($notif->judul), 'lunas')) {
                        $icon = 'fa-circle-check';
                        $iconStyle = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                    } elseif ($notif->tipe === 'bukti_pembayaran' || Str::contains(strtolower($notif->judul), 'bukti')) {
                        $icon = 'fa-receipt';
                        $iconStyle = 'bg-amber-50 text-amber-600 border-amber-100';
                    } elseif ($isCatering) {
                        $icon = 'fa-box-open';
                        $iconStyle = 'bg-purple-50 text-purple-600 border-purple-100';
                    }
                @endphp
                <a href="{{ $notif->link ? url($notif->link) : '#' }}" 
                   class="block p-3.5 hover:bg-gray-50/80 transition-colors {{ $notif->is_read ? 'bg-white opacity-75' : 'bg-emerald-50/20 font-medium' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl border flex items-center justify-center text-xs shrink-0 mt-0.5 {{ $iconStyle }}">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <p class="text-[12px] font-extrabold text-gray-900 truncate leading-tight">{{ $notif->judul }}</p>
                                <span class="mono text-[10px] text-gray-400 font-semibold shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-[11px] text-gray-500 leading-snug line-clamp-2">{{ $notif->pesan }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="py-10 px-4 text-center text-gray-400 text-xs">
                    <div class="w-10 h-10 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-2 text-gray-400">
                        <i class="fa-solid fa-bell-slash text-sm"></i>
                    </div>
                    <p class="font-bold text-gray-600 text-xs">Tidak Ada Notifikasi</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Semua notifikasi terbaru akan muncul di sini</p>
                </div>
            @endforelse
        </div>

        {{-- Footer Minimalis --}}
        <a href="{{ route('admin.notifikasi.index') }}" 
           class="block p-3 bg-gray-50/90 text-center text-xs font-extrabold text-[#0F2E23] hover:bg-emerald-50 transition-colors border-t border-gray-100 flex items-center justify-center gap-1.5">
            <span>Lihat Semua Notifikasi</span>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </a>
    </div>
</div>
