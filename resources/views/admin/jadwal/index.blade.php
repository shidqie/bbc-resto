@extends('layouts.pos')

@section('title', 'Jadwal Pengantaran')

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full max-w-7xl mx-auto flex flex-col md:flex-row gap-6" x-data="jadwalApp()">
    
    <!-- Left Panel: Mini Calendar & Summary -->
    <div class="w-full md:w-80 flex-shrink-0 flex flex-col gap-6">
        <!-- Calendar Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-gray-800">Kalender</h2>
                <div class="flex gap-2">
                    <a href="{{ route('admin.jadwal.index', ['month' => \Carbon\Carbon::parse($selectedMonth)->subMonth()->format('Y-m'), 'date' => $selectedDate]) }}" class="p-1 rounded-md hover:bg-gray-100 text-gray-500">
                        <x-heroicon-o-chevron-left class="w-5 h-5" />
                    </a>
                    <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($selectedMonth)->translatedFormat('F Y') }}</span>
                    <a href="{{ route('admin.jadwal.index', ['month' => \Carbon\Carbon::parse($selectedMonth)->addMonth()->format('Y-m'), 'date' => $selectedDate]) }}" class="p-1 rounded-md hover:bg-gray-100 text-gray-500">
                        <x-heroicon-o-chevron-right class="w-5 h-5" />
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-gray-500 mb-2">
                <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
            </div>
            <div class="grid grid-cols-7 gap-1">
                @php
                    $daysInMonth = $startOfMonth->daysInMonth;
                    $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
                @endphp
                
                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="p-2 text-center text-gray-300"></div>
                @endfor
                
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $currentDateStr = $startOfMonth->copy()->addDays($day - 1)->format('Y-m-d');
                        $hasOrder = isset($orderDates[$currentDateStr]) && $orderDates[$currentDateStr] > 0;
                        $isSelected = $currentDateStr === $selectedDate;
                        $isToday = $currentDateStr === now()->format('Y-m-d');
                    @endphp
                    <a href="{{ route('admin.jadwal.index', ['date' => $currentDateStr, 'month' => $selectedMonth]) }}" 
                       class="relative flex flex-col items-center justify-center p-2 rounded-full aspect-square text-sm transition-all
                              {{ $isSelected ? 'bg-primary text-white font-bold shadow-md' : 'text-gray-700 hover:bg-gray-100' }}
                              {{ $isToday && !$isSelected ? 'border border-primary text-primary font-bold' : '' }}">
                        <span>{{ $day }}</span>
                        @if($hasOrder)
                            <span class="absolute bottom-1 w-1.5 h-1.5 rounded-full {{ $isSelected ? 'bg-white' : 'bg-orange-500' }}"></span>
                        @endif
                    </a>
                @endfor
            </div>
        </div>

        <!-- Summary Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-bold text-gray-800 mb-4">Ringkasan Harian</h2>
            <div class="text-sm text-gray-500 mb-4">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</div>
            <div class="flex flex-col gap-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-blue-500"></div>Semua Pesanan</span>
                    <span class="font-bold">{{ $summary['Semua'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-orange-500"></div>Diproses</span>
                    <span class="font-bold">{{ $summary['diproses'] + $summary['menunggu_konfirmasi'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-purple-500"></div>Sedang Diantar</span>
                    <span class="font-bold">{{ $summary['dikirim'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-green-500"></div>Selesai</span>
                    <span class="font-bold">{{ $summary['selesai'] + $summary['lunas'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Timeline & List -->
    <div class="flex-1 flex flex-col min-h-0 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        
        <!-- Toolbar & Filters -->
        <div class="border-b border-gray-200 p-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <h1 class="text-xl font-bold text-gray-800">Jadwal Pengantaran</h1>
                <form action="{{ route('admin.jadwal.index') }}" method="GET" class="relative w-full md:w-64">
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ID atau nama..." class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                    <button type="submit" class="absolute left-3 top-2.5">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400" />
                    </button>
                </form>
            </div>
            
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                @php
                    $tabs = [
                        'Semua' => 'Semua',
                        'menunggu_konfirmasi' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'dikirim' => 'Diantar',
                        'selesai' => 'Selesai'
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('admin.jadwal.index', ['date' => $selectedDate, 'month' => $selectedMonth, 'status' => $key, 'search' => request('search')]) }}" 
                       class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-2
                              {{ $statusFilter === $key ? 'bg-primary text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                        @if($key !== 'Semua' && isset($summary[$key]) && $summary[$key] > 0)
                            <span class="{{ $statusFilter === $key ? 'bg-white text-primary' : 'bg-gray-300 text-gray-700' }} text-xs py-0.5 px-2 rounded-full font-bold">
                                {{ $summary[$key] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Timeline List -->
        <div class="flex-1 overflow-y-auto p-4 bg-gray-50/50">
            @if(count($orders) > 0)
                <div class="relative border-l-2 border-gray-200 ml-4 pl-6 py-2 space-y-8">
                    @foreach($orders as $order)
                        <div class="relative group cursor-pointer" @click="openDrawer({{ json_encode($order) }})">
                            <!-- Timeline Dot -->
                            <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full border-4 border-white bg-primary shadow-sm group-hover:scale-125 transition-transform"></div>
                            
                            <!-- Card -->
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow group-hover:border-primary/30">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="font-bold text-lg text-primary">{{ $order->waktu_acara ? \Carbon\Carbon::parse($order->waktu_acara)->format('H:i') : 'Waktu Belum Diset' }}</span>
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wide bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $order->jenis }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $order->kode_pesanan }}</span>
                                </div>
                                
                                <h3 class="font-bold text-gray-800 text-lg">{{ $order->nama_pemesan }}</h3>
                                
                                <div class="mt-3 text-sm text-gray-600 flex items-start gap-2">
                                    <x-heroicon-o-map-pin class="w-4 h-4 mt-0.5 text-gray-400 shrink-0" />
                                    <span>{{ $order->jenis == 'Catering' ? $order->lokasi_acara : $order->alamat }}</span>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                    <div class="text-sm font-medium text-gray-700">
                                        {{ $order->paket->nama_paket ?? 'Paket Kustom' }} 
                                        <span class="text-gray-400 mx-1">•</span> 
                                        {{ $order->jenis == 'Catering' ? $order->jumlah_porsi . ' Porsi' : $order->jumlah_box . ' Box' }}
                                    </div>
                                    
                                    <div>
                                        @if(in_array($order->status, ['diproses', 'menunggu_konfirmasi']))
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Diproses
                                            </span>
                                        @elseif($order->status == 'dikirim')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span> Diantar
                                            </span>
                                        @elseif(in_array($order->status, ['selesai', 'lunas']))
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                <x-heroicon-o-check class="w-3 h-3" /> Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-full flex flex-col items-center justify-center text-center px-4 py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <x-heroicon-o-calendar-days class="w-10 h-10 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Tidak ada jadwal</h3>
                    <p class="text-gray-500 max-w-sm">Belum ada jadwal pesanan untuk tanggal ini atau filter yang dipilih kosong.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Drawer Overlay -->
    <div x-show="drawerOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/40 z-40" @click="drawerOpen = false" style="display: none;"></div>

    <!-- Slide-over Drawer for Order Details & Stepper -->
    <div x-show="drawerOpen" 
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 w-full md:w-[480px] bg-white shadow-2xl z-50 flex flex-col border-l border-gray-200" style="display: none;">
         
        <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <div>
                <div class="text-sm font-medium text-gray-500 mb-1" x-text="activeOrder?.kode_pesanan"></div>
                <h2 class="text-xl font-bold text-gray-800" x-text="activeOrder?.nama_pemesan"></h2>
            </div>
            <button @click="drawerOpen = false" class="p-2 bg-white rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-colors">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            
            <!-- Info List -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 mb-8 space-y-4">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <x-heroicon-o-clock class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-0.5">Waktu Pengantaran</div>
                        <div class="font-bold text-gray-900" x-text="formatTime(activeOrder?.waktu_acara)"></div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                        <x-heroicon-o-phone class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-0.5">Kontak</div>
                        <div class="font-bold text-gray-900" x-text="activeOrder?.kontak"></div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                        <x-heroicon-o-map-pin class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-0.5">Alamat / Lokasi</div>
                        <div class="font-bold text-gray-900 text-sm leading-relaxed" x-text="activeOrder?.jenis === 'Catering' ? activeOrder?.lokasi_acara : activeOrder?.alamat"></div>
                    </div>
                </div>
            </div>

            <h3 class="font-bold text-gray-800 mb-4 text-lg">Perbarui Status</h3>
            
            <!-- Interactive Stepper -->
            <div class="relative ml-4 border-l-2 border-gray-200 space-y-6">
                <!-- Diproses -->
                <div class="relative cursor-pointer group" @click="updateStatus('diproses')">
                    <div class="absolute -left-[25px] top-1 w-12 h-12 rounded-full border-4 border-white flex items-center justify-center transition-colors"
                         :class="isStatusReached('diproses') ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-400 group-hover:bg-gray-300'">
                        <x-heroicon-o-fire class="w-5 h-5" />
                    </div>
                    <div class="pl-10 pb-2">
                        <h4 class="font-bold text-base transition-colors" :class="isStatusReached('diproses') ? 'text-gray-900' : 'text-gray-500'">Dapur Memproses</h4>
                        <p class="text-sm text-gray-500">Makanan sedang disiapkan / dimasak.</p>
                    </div>
                </div>

                <!-- Diantar -->
                <div class="relative cursor-pointer group" @click="updateStatus('dikirim')">
                    <div class="absolute -left-[25px] top-1 w-12 h-12 rounded-full border-4 border-white flex items-center justify-center transition-colors"
                         :class="isStatusReached('dikirim') ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-400 group-hover:bg-gray-300'">
                        <x-heroicon-o-truck class="w-5 h-5" />
                    </div>
                    <div class="pl-10 pb-2">
                        <h4 class="font-bold text-base transition-colors" :class="isStatusReached('dikirim') ? 'text-gray-900' : 'text-gray-500'">Sedang Diantar</h4>
                        <p class="text-sm text-gray-500">Kurir sedang dalam perjalanan ke lokasi.</p>
                    </div>
                </div>

                <!-- Selesai -->
                <div class="relative cursor-pointer group" @click="updateStatus('selesai')">
                    <div class="absolute -left-[25px] top-1 w-12 h-12 rounded-full border-4 border-white flex items-center justify-center transition-colors"
                         :class="isStatusReached('selesai') ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400 group-hover:bg-gray-300'">
                        <x-heroicon-o-check class="w-6 h-6" />
                    </div>
                    <div class="pl-10 pb-2">
                        <h4 class="font-bold text-base transition-colors" :class="isStatusReached('selesai') ? 'text-gray-900' : 'text-gray-500'">Selesai / Terkirim</h4>
                        <p class="text-sm text-gray-500">Pesanan telah tiba dan diterima pelanggan.</p>
                    </div>
                </div>
            </div>

            <!-- Toast Notification -->
            <div x-show="showToast" x-transition class="mt-8 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center gap-3" style="display: none;">
                <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 shrink-0" />
                <span class="font-medium text-sm" x-text="toastMessage"></span>
            </div>

            <div class="mt-8">
                <a :href="activeOrder?.jenis === 'Catering' ? '/admin/pesanan/catering/' + activeOrder?.id : '/admin/pesanan/nasi-box/' + activeOrder?.id" class="w-full block text-center py-3 border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                    Lihat Detail Pesanan Penuh
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    function jadwalApp() {
        return {
            drawerOpen: false,
            activeOrder: null,
            showToast: false,
            toastMessage: '',
            statusOrder: ['menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'diproses', 'dikirim', 'selesai', 'lunas', 'dibatalkan'],

            openDrawer(order) {
                this.activeOrder = order;
                this.drawerOpen = true;
                this.showToast = false;
            },

            formatTime(timeStr) {
                if(!timeStr) return "Waktu Belum Diset";
                // timeStr is like "10:30:00"
                const parts = timeStr.split(':');
                if(parts.length >= 2) return parts[0] + ':' + parts[1];
                return timeStr;
            },

            isStatusReached(statusName) {
                if(!this.activeOrder) return false;
                const targetIdx = this.statusOrder.indexOf(statusName);
                const currentIdx = this.statusOrder.indexOf(this.activeOrder.status);
                
                // If it's lunas, it implies selesai was reached
                if (this.activeOrder.status === 'lunas' && ['diproses', 'dikirim', 'selesai'].includes(statusName)) {
                    return true;
                }
                
                return currentIdx >= targetIdx;
            },

            async updateStatus(newStatus) {
                if(!this.activeOrder) return;
                
                try {
                    const response = await fetch(`/admin/jadwal/${this.activeOrder.jenis}/${this.activeOrder.id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });
                    
                    const data = await response.json();
                    
                    if(data.success) {
                        this.activeOrder.status = data.new_status;
                        this.toastMessage = data.message;
                        this.showToast = true;
                        
                        setTimeout(() => {
                            this.showToast = false;
                            // Optionally reload to update main view timeline
                            window.location.reload();
                        }, 1500);
                    }
                } catch(error) {
                    console.error("Error updating status:", error);
                    alert("Gagal memperbarui status.");
                }
            }
        }
    }
</script>
@endsection
