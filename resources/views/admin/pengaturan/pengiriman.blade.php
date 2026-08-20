@extends('layouts.pos')

@section('title', 'Pengaturan Pengiriman')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5" x-data="pengaturanPengiriman()">
        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Pengaturan Pengiriman" subtitle="Kelola tarif dan ketentuan biaya pengiriman Katering & Nasi Box." :breadcrumbs="['Pengaturan', 'Tarif Pengiriman']">
        </x-ui.page-header>

        @if (session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-xl flex items-center gap-3">
            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
        @endif
        
        @if ($errors->any())
        <div class="p-4 bg-red-50 text-red-700 rounded-xl">
            <ul class="list-disc list-inside text-sm font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            {{-- Form Pengaturan --}}
            <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm shadow-gray-100/50">
                <form action="{{ route('admin.pengaturan.pengiriman.update') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-8">
                        {{-- Tarif Dasar --}}
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Tarif Pengiriman</h2>
                            <p class="text-sm text-gray-500 mt-1 mb-4">Tarif dasar flat (rata) dan tarif tambahan per kilometer.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
                                <div>
                                    <x-ui.input type="number" name="tarif_dasar" label="Tarif Dasar/Flat (Rp)" x-model.number="tarifDasar" :error="$errors->first('tarif_dasar')" />
                                </div>
                                <div>
                                    <x-ui.input type="number" name="tarif_per_km" label="Tarif Tambahan per Kilometer (Rp)" x-model.number="tarifPerKm" :error="$errors->first('tarif_per_km')" />
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-50">

                        {{-- Aturan Gratis Ongkir --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900">Aturan Gratis Pengiriman</h2>
                                    <p class="text-sm text-gray-500 mt-1">Tentukan jarak gratis pengiriman berdasarkan jumlah porsi pesanan.</p>
                                </div>
                                <button type="button" @click="tambahAturan()" class="text-sm font-medium text-primary hover:text-primary flex items-center gap-1 bg-primary-soft px-3 py-1.5 rounded-lg transition-colors">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                    <span>Tambah Aturan</span>
                                </button>
                            </div>

                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Jumlah Porsi Minimum</th>
                                            <th class="px-4 py-3 font-semibold">Jumlah Porsi Maksimum</th>
                                            <th class="px-4 py-3 font-semibold">Jarak Gratis</th>
                                            <th class="px-4 py-3 w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="(item, index) in aturan" :key="index">
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <input type="hidden" :name="`aturan[${index}][id]`" x-model="item.id">
                                                    <input type="number" x-bind:name="`aturan[${index}][minimal_porsi]`" x-model.number="item.minimal_porsi" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary transition-colors" required>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" x-bind:name="`aturan[${index}][maksimal_porsi]`" x-model="item.maksimal_porsi" placeholder="Tidak terbatas" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary transition-colors">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <input type="number" step="0.01" x-bind:name="`aturan[${index}][kilometer_gratis]`" x-model.number="item.kilometer_gratis" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary transition-colors" required>
                                                        <span class="text-gray-500 font-medium">km</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <button type="button" @click="hapusAturan(index)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                        <x-heroicon-o-trash class="w-4 h-4" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="aturan.length === 0">
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                                Belum ada aturan pengiriman yang dikonfigurasi.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <x-ui.button type="submit" variant="primary">
                            <x-heroicon-o-document-check class="w-4 h-4 mr-1" />
                            Simpan Perubahan
                        </x-ui.button>
                    </div>
                </form>
            </div>

            {{-- Simulasi Tagihan --}}
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm shadow-gray-100/50 h-full flex flex-col">
                    <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <x-heroicon-o-calculator class="w-5 h-5 text-primary" />
                        Simulasi Perhitungan
                    </h2>
                    
                    <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-100 flex-1">
                        <div class="space-y-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Input Contoh Jarak (Km)</label>
                                <input type="number" x-model.number="simulasiJarak" class="block w-full rounded-lg border-gray-200 text-sm px-3 py-1.5 focus:border-primary focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Input Contoh Jumlah Porsi</label>
                                <input type="number" x-model.number="simulasiPorsi" class="block w-full rounded-lg border-gray-200 text-sm px-3 py-1.5 focus:border-primary focus:ring-primary">
                            </div>
                        </div>
                        
                        <div class="space-y-2 pt-3 border-t border-gray-200">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Gratis Jarak Berdasarkan Porsi</span>
                                <span class="font-medium text-green-600"><span x-text="dapatGratisJarak"></span> Km</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Jarak yang Dihitung (Berbayar)</span>
                                <span class="font-medium text-gray-900"><span x-text="jarakBerbayar"></span> Km</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tarif Dasar (Flat)</span>
                                <span class="font-medium text-gray-900" x-text="'Rp ' + (tarifDasar || 0).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tarif Tambahan per Km</span>
                                <span class="font-medium text-gray-900" x-text="'Rp ' + (tarifPerKm || 0).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                        
                        <hr class="border-gray-200 my-4 border-dashed">
                        
                        <div class="flex justify-between items-center text-base">
                            <span class="font-bold text-gray-900">Total Ongkir</span>
                            <span class="font-black text-primary text-lg" x-text="'Rp ' + totalOngkir.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Aktual Saat Ini --}}
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm shadow-gray-100/50 h-full flex flex-col">
                    <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                        Konfigurasi Aktif
                    </h2>
                    
                    <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-100 flex-1 flex flex-col">
                        <p class="text-xs text-gray-500 mb-4 pb-3 border-b border-gray-200">Pengaturan yang sedang aktif di sistem.</p>
                        
                        <div class="space-y-4 flex-1">
                            <div>
                                <span class="block text-xs font-medium text-gray-500 mb-1">Tarif Dasar (Flat)</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 text-sm">Rp {{ number_format($pengaturan->tarif_dasar ?? 0, 0, ',', '.') }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-gray-500 mb-1">Tarif Tambahan per Km</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 text-sm">Rp {{ number_format($pengaturan->tarif_per_km ?? 0, 0, ',', '.') }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                </div>
                            </div>
                            
                            @if(count($aturan) > 0)
                            <div class="pt-3 border-t border-gray-200">
                                <span class="block text-xs font-medium text-gray-500 mb-2">Aturan Jarak Gratis:</span>
                                <ul class="space-y-2">
                                    @foreach($aturan as $a)
                                    <li class="text-xs text-gray-700 flex justify-between items-center bg-white p-2 rounded border border-gray-100 shadow-sm">
                                        <span>
                                            <span class="font-medium">{{ $a->minimal_porsi }}</span>
                                            @if($a->maksimal_porsi)
                                                - <span class="font-medium">{{ $a->maksimal_porsi }}</span> porsi
                                            @else
                                                porsi ke atas
                                            @endif
                                        </span>
                                        <span class="font-semibold text-green-600">Gratis {{ $a->kilometer_gratis }} Km</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            
                        </div>
                        
                        @if(count($riwayats) > 0)
                        <div class="pt-3 mt-4 border-t border-gray-200">
                            <span class="block text-[10px] text-gray-400 mb-0.5">Terakhir diperbarui</span>
                            <div class="text-xs font-medium text-gray-700">
                                {{ $riwayats->first()->dibuat_pada->format('d F Y, H:i') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                Oleh {{ $riwayats->first()->diubahOleh->nama ?? 'Sistem' }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Riwayat Perubahan --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm shadow-gray-100/50 overflow-hidden mt-6">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/30 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Riwayat Perubahan</h2>
                    <p class="text-xs text-gray-500 mt-1">Daftar historis perubahan pengaturan pengiriman.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Pengaturan</th>
                            <th class="px-6 py-4">Sebelumnya</th>
                            <th class="px-6 py-4">Menjadi</th>
                            <th class="px-6 py-4">Diubah Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($riwayats as $riwayat)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $riwayat->dibuat_pada->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $riwayat->dibuat_pada->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-700">
                                <div>Tarif Dasar</div>
                                <div>Tarif per Km</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div>Rp {{ number_format($riwayat->nilai_lama['tarif_dasar'] ?? 0, 0, ',', '.') }}</div>
                                <div>Rp {{ number_format($riwayat->nilai_lama['tarif_per_km'] ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <div>Rp {{ number_format($riwayat->nilai_baru['tarif_dasar'] ?? 0, 0, ',', '.') }}</div>
                                <div>Rp {{ number_format($riwayat->nilai_baru['tarif_per_km'] ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-primary-soft text-primary flex items-center justify-center font-bold text-[10px]">
                                        {{ strtoupper(substr($riwayat->diubahOleh->nama ?? 'S', 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-gray-700">{{ $riwayat->diubahOleh->nama ?? 'Sistem' }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <x-heroicon-o-clock class="w-10 h-10 mb-3 opacity-20" />
                                    <p class="text-sm font-medium text-gray-500">Belum ada riwayat perubahan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($riwayats instanceof \Illuminate\Pagination\LengthAwarePaginator && $riwayats->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $riwayats->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<script>
    function pengaturanPengiriman() {
        return {
            tarifDasar: {{ old('tarif_dasar', $pengaturan->tarif_dasar ?? 0) }},
            tarifPerKm: {{ old('tarif_per_km', $pengaturan->tarif_per_km ?? 0) }},
            aturan: @json($aturan ?? []),
            simulasiJarak: 10,
            simulasiPorsi: 50,
            tambahAturan() {
                this.aturan.push({
                    id: '',
                    minimal_porsi: 0,
                    maksimal_porsi: '',
                    kilometer_gratis: 0
                });
            },
            hapusAturan(index) {
                this.aturan.splice(index, 1);
            },
            get dapatGratisJarak() {
                let porsi = this.simulasiPorsi;
                let gratis = 0;
                
                // Urutkan dari syarat porsi tertinggi (memastikan aturan yang lebih besar tertangkap dulu jika diproses manual)
                // Sebenarnya loop saja dan cari yang match
                let aturanMatch = null;
                for (let i = 0; i < this.aturan.length; i++) {
                    let rule = this.aturan[i];
                    let min = parseFloat(rule.minimal_porsi) || 0;
                    let max = rule.maksimal_porsi ? parseFloat(rule.maksimal_porsi) : Infinity;
                    
                    if (porsi >= min && porsi <= max) {
                        let ruleGratis = parseFloat(rule.kilometer_gratis) || 0;
                        if (aturanMatch === null || rule.minimal_porsi > aturanMatch.minimal_porsi) {
                            aturanMatch = rule;
                        }
                    }
                }
                
                if (aturanMatch) {
                    gratis = parseFloat(aturanMatch.kilometer_gratis) || 0;
                }
                
                return gratis;
            },
            get jarakBerbayar() {
                let sisa = this.simulasiJarak - this.dapatGratisJarak;
                return sisa > 0 ? sisa : 0;
            },
            get totalOngkir() {
                return this.tarifDasar + (this.jarakBerbayar * (this.tarifPerKm || 0));
            }
        }
    }
</script>
@endsection
