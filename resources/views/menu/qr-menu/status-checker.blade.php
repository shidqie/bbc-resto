<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cek Status Pesanan — Meja {{ $meja ? (Str::startsWith($meja->nomor_meja,'Meja') ? $meja->nomor_meja : 'Meja '.$meja->nomor_meja) : 'BBC Resto' }}</title>
    <meta name="description" content="Cek status pesanan Dine In BBC Resto">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    fontSize: {
                        xs: ['13px', '1.45'],
                        sm: ['15px', '1.5'],
                        base: ['16px', '1.55'],
                        lg: ['18px', '1.5'],
                        xl: ['20px', '1.4'],
                        '2xl': ['24px', '1.3'],
                        '3xl': ['30px', '1.25'],
                        '4xl': ['36px', '1.2'],
                        '5xl': ['48px', '1.15'],
                        '6xl': ['60px', '1.1'],
                    },
                    colors: {
                        brand:   '#171717',
                        accent:  '#525252',
                        surface: '#F7F7F7',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak]{display:none!important}
        .no-scrollbar::-webkit-scrollbar{display:none}
        .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .status-badge {
            @apply inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold;
        }
        .status-masuk { @apply bg-blue-100 text-blue-800; }
        .status-dikonfirmasi { @apply bg-indigo-100 text-indigo-800; }
        .status-diproses { @apply bg-amber-100 text-amber-800; }
        .status-siap { @apply bg-emerald-100 text-emerald-800; }
        .status-selesai { @apply bg-slate-100 text-slate-800; }
        .status-dibatalkan { @apply bg-red-100 text-red-800; }
        .kot-status-masuk { @apply bg-neutral-100 text-neutral-800; }
        .kot-status-diproses { @apply bg-amber-100 text-amber-800; }
        .kot-status-selesai { @apply bg-emerald-100 text-emerald-800; }
    </style>
</head>

<body class="bg-surface text-gray-900 antialiased" x-data="orderStatusChecker()" x-init="init()" x-cloak>

{{-- ═══ STICKY HEADER ═══ --}}
<header class="bg-white sticky top-0 z-40 border-b border-neutral-200">
    <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
        {{-- Brand --}}
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-neutral-900 flex items-center justify-center">
                <x-heroicon-o-shopping-bag class="w-4 h-4 text-neutral-400" />
            </div>
            <div>
                <p class="text-sm font-semibold text-neutral-900 leading-none">BBC Resto</p>
                <p class="text-xs text-neutral-400 mt-0.5 leading-none">Status Pesanan</p>
            </div>
        </div>
        {{-- Meja Badge --}}
        <div class="flex items-center gap-1.5">
            <div class="h-8 px-3 rounded-xl bg-neutral-900 text-white text-xs font-medium flex items-center gap-1.5">
                <x-heroicon-o-users class="w-3 h-3 opacity-70" />
                <span>{{ $meja ? (Str::startsWith($meja->nomor_meja,'Meja') ? $meja->nomor_meja : 'Meja '.$meja->nomor_meja) : '—' }}</span>
            </div>
        </div>
    </div>
</header>

{{-- ═══ MAIN CONTENT ═══ --}}
<main class="max-w-lg mx-auto px-4 pt-6 pb-24">

    {{-- Loading State --}}
    <div x-show="loading" class="py-20 text-center">
        <div class="w-10 h-10 border-3 border-neutral-200 border-t-neutral-900 rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-sm text-neutral-500">Memuat status pesanan...</p>
    </div>

    {{-- No Order State --}}
    <div x-show="!loading && !hasOrder" class="py-20 text-center">
        <div class="w-20 h-20 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4 text-neutral-300">
            <x-heroicon-o-shopping-bag class="w-10 h-10" />
        </div>
        <h2 class="text-lg font-semibold text-neutral-900 mb-2">Belum Ada Pesanan</h2>
        <p class="text-sm text-neutral-500 mb-6">Anda belum memesan apapun di meja ini.</p>
        <a :href="'/qr-menu/'+mejaToken"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-neutral-900 text-white rounded-xl text-sm font-semibold hover:bg-neutral-700 transition-colors">
            <x-heroicon-o-plus class="w-4 h-4" />
            Pesan Sekarang
        </a>
    </div>

    {{-- Order Active State --}}
    <div x-show="!loading && hasOrder" class="space-y-4">

        {{-- Order Header --}}
        <div class="bg-white rounded-lg border border-neutral-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-neutral-900">Pesanan Aktif</h3>
                <span class="mono text-xs font-mono text-neutral-500" x-text="pesanan.nomor_pesanan"></span>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user class="w-4 h-4 text-neutral-400" />
                    <span class="text-sm font-medium text-neutral-700" x-text="pesanan.nama_konsumen || 'Tamu'"></span>
                </div>
                <template x-if="pesanan.status_pesanan_id">
                    <span :class="['status-badge', getStatusClass(pesanan.status_pesanan_id)]"
                          x-text="pesanan.status_label"></span>
                </template>
            </div>
        </div>

        {{-- KOT Status (Kitchen Progress) --}}
        <template x-if="kot">
        <div class="bg-white rounded-lg border border-neutral-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-neutral-900 flex items-center gap-2">
                    <x-heroicon-o-fire class="w-4 h-4 text-amber-500" />
                    Status Dapur
                </h3>
                <span class="mono text-xs font-mono text-neutral-500" x-text="kot.nomor_tiket"></span>
            </div>

            {{-- Progress Steps --}}
            <div class="space-y-3">
                <template x-for="step in kotSteps" :key="step.id">
                    <div class="flex items-start gap-3">
                        <div class="flex flex-col items-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all',
                                step.completed ? 'bg-emerald-500 border-emerald-500 text-white' :
                                step.active ? 'bg-amber-500 border-amber-500 text-white' :
                                'bg-white border-neutral-200 text-neutral-400']"
                                 x-text="step.icon"></div>
                            <div class="h-6 w-0.5 bg-neutral-200" x-show="!$last"></div>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-sm font-medium text-neutral-900" x-text="step.label"></p>
                            <p class="text-xs text-neutral-400" x-text="step.time || '—'"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Current KOT Status Badge --}}
            <div class="mt-4 pt-3 border-t border-neutral-100">
                <span :class="['status-badge', getKotStatusClass(kot.status_tiket_dapur_id)]"
                      x-text="kot.status_label"></span>
            </div>
        </div>
        </template>

        {{-- Order Items --}}
        <div class="bg-white rounded-lg border border-neutral-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-neutral-100">
                <h3 class="text-sm font-semibold text-neutral-900">Detail Pesanan</h3>
            </div>
            <div class="divide-y divide-neutral-100">
                <template x-for="item in pesanan.items" :key="item.nama">
                    <div class="px-4 py-3 flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-neutral-900" x-text="item.nama"></p>
                            <p class="text-xs text-neutral-500" x-text="'Qty: ' + item.qty + ' × ' + formatPrice(item.harga)"></p>
                            <template x-if="item.catatan">
                                <p class="text-xs text-amber-600 italic mt-1" x-text="'📝 ' + item.catatan"></p>
                            </template>
                        </div>
                        <span class="text-sm font-semibold text-neutral-900 whitespace-nowrap" x-text="formatPrice(item.subtotal)"></span>
                    </div>
                </template>
            </div>

            {{-- Total --}}
            <div class="px-4 py-3 bg-neutral-50 border-t border-neutral-100">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-neutral-700">Total Tagihan</span>
                    <span class="font-bold text-neutral-900" x-text="formatPrice(pesanan.total_tagihan)"></span>
                </div>
            </div>
        </div>

        {{-- Payment Status --}}
        <template x-if="payment">
        <div class="bg-white rounded-lg border border-neutral-200 p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-semibold text-neutral-900 flex items-center gap-2">
                    <x-heroicon-o-credit-card class="w-4 h-4 text-emerald-500" />
                    Status Pembayaran
                </h3>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-700" x-text="payment.metode"></p>
                    <p class="text-xs text-neutral-400" x-text="payment.status"></p>
                </div>
                <template x-if="payment.status === 'Lunas' || payment.status === 'LUNAS'">
                    <span class="status-badge bg-emerald-100 text-emerald-800">Lunas</span>
                </template>
                <template x-if="payment.status !== 'Lunas' && payment.status !== 'LUNAS'">
                    <span class="status-badge bg-amber-100 text-amber-800">Menunggu Pembayaran</span>
                </template>
            </div>
        </div>
        </template>

        {{-- Refresh Button --}}
        <div class="text-center pt-4">
            <button @click="refresh()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-neutral-100 text-neutral-700 rounded-xl text-sm font-semibold hover:bg-neutral-200 transition-colors">
                <x-heroicon-o-arrow-path class="w-4 h-4" />
                Refresh Status
            </button>
        </div>

    </div>

</main>

{{-- ═══ ALPINE LOGIC ═══ --}}
<script>
function orderStatusChecker() {
    return {
        loading: true,
        hasOrder: false,
        mejaToken: '{{ $meja->qr_token ?? "" }}',
        pesanan: {},
        kot: null,
        payment: null,
        kotSteps: [
            { id: 1, label: 'Pesanan Masuk', icon: '📥', completed: false, active: false },
            { id: 2, label: 'Sedang Dimasak', icon: '🍳', completed: false, active: false },
            { id: 3, label: 'Siap Disajikan', icon: '✅', completed: false, active: false },
        ],

        async init() {
            await this.refresh();
            // Auto-refresh every 10 seconds
            this.refreshInterval = setInterval(() => this.refresh(), 10000);
        },

        async refresh() {
            this.loading = true;
            try {
                const res = await fetch(`/qr-menu/${this.mejaToken}/status`);
                const data = await res.json();

                if (data.success) {
                    this.hasOrder = data.has_order;
                    if (data.has_order) {
                        this.pesanan = data.pesanan;
                        this.kot = data.kot;
                        this.payment = data.payment;
                        this.updateKotSteps();
                    }
                }
            } catch (e) {
                console.error('Failed to fetch order status:', e);
            } finally {
                this.loading = false;
            }
        },

        updateKotSteps() {
            if (!this.kot) return;

            const status = this.kot.status_tiket_dapur_id;
            // 1 = Menunggu, 2 = Diproses, 3 = Selesai
            this.kotSteps[0].completed = status >= 1;
            this.kotSteps[0].active = status === 1;
            this.kotSteps[1].completed = status >= 2;
            this.kotSteps[1].active = status === 2;
            this.kotSteps[2].completed = status === 3;
            this.kotSteps[2].active = false;

            // Update times
            if (this.kot.diproses_pada) this.kotSteps[1].time = this.kot.diproses_pada;
            if (this.kot.siap_pada) this.kotSteps[2].time = this.kot.siap_pada;
        },

        getStatusClass(statusId) {
            const classes = {
                1: 'status-masuk',
                2: 'status-dikonfirmasi',
                3: 'status-diproses',
                4: 'status-siap',
                5: 'status-selesai',
                6: 'status-dibatalkan',
            };
            return classes[statusId] || 'status-masuk';
        },

        getKotStatusClass(statusId) {
            const classes = {
                1: 'kot-status-masuk',
                2: 'kot-status-diproses',
                3: 'kot-status-selesai',
            };
            return classes[statusId] || 'kot-status-masuk';
        },

        formatPrice(price) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(price);
        },
    };
}
</script>
</body>
</html>