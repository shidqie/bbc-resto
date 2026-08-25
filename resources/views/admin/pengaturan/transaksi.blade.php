@extends('layouts.pos')

@section('title', 'Pengaturan Transaksi')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        {{-- PAGE HEADER --}}
        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Pengaturan Transaksi" subtitle="Kelola biaya layanan yang diterapkan pada transaksi Dine-In." :breadcrumbs="['Pengaturan', 'Biaya Layanan']">
        </x-ui.page-header>

        @if (session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-xl flex items-center gap-3">
            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
        @endif

        <div x-data="{
            layananAktif: {{ old('layanan_aktif', $pengaturan->layanan_aktif ?? false) ? 'true' : 'false' }},
            nominalLayanan: {{ old('nominal_layanan', (float) ($pengaturan->nominal_layanan ?? 1000)) }},
            subtotal: 100000,
            get totalLayanan() { return this.layananAktif ? this.nominalLayanan : 0; },
            get totalTagihan() { return this.subtotal + this.totalLayanan; }
        }">
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                {{-- Form Pengaturan --}}
                <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm shadow-gray-100/50">
                    <form action="{{ route('admin.pengaturan.transaksi.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="pajak_aktif" value="0">
                        <input type="hidden" name="persentase_pajak" value="0">
                        
                        <div class="space-y-8">
                            {{-- Biaya Layanan --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-1">
                                    <h2 class="text-base font-semibold text-gray-900">Biaya Layanan (Service Charge)</h2>
                                    <p class="text-sm text-gray-500 mt-1">Biaya layanan flat per transaksi/struk yang dikenakan pada pesanan Dine-In.</p>
                                </div>
                                <div class="md:col-span-1 space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="layanan_aktif" value="1" x-model="layananAktif" class="w-5 h-5 text-primary rounded border-gray-300 focus:ring-primary">
                                        <span class="text-sm font-medium text-gray-700">Aktifkan Biaya Layanan</span>
                                    </label>
                                    
                                    <div x-show="layananAktif" x-transition>
                                        <x-ui.input type="number" name="nominal_layanan" label="Nominal per Transaksi / Struk (Rp)" x-model.number="nominalLayanan" :error="$errors->first('nominal_layanan')" />
                                    </div>
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
                            <p class="text-xs text-gray-500 mb-4 pb-3 border-b border-gray-200">Contoh transaksi Dine-In jika subtotal adalah Rp 100.000</p>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-semibold text-gray-900">Rp 100.000</span>
                                </div>
                                <div class="flex justify-between text-xs" x-show="layananAktif" x-transition>
                                    <span class="text-gray-600">Biaya Layanan (Per Struk)</span>
                                    <span class="font-semibold text-gray-900" x-text="'Rp ' + (totalLayanan || 0).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                            
                            <hr class="border-gray-200 my-4 border-dashed">
                            
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-bold text-gray-900">Total Tagihan</span>
                                <span class="font-black text-primary text-base" x-text="'Rp ' + totalTagihan.toLocaleString('id-ID')"></span>
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
                                    <span class="block text-xs font-medium text-gray-500 mb-1">Biaya Layanan (Per Struk)</span>
                                    @if($pengaturan->layanan_aktif ?? false)
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 text-sm">Rp {{ number_format($pengaturan->nominal_layanan ?? 1000, 0, ',', '.') }}</span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-primary-soft text-primary border border-primary/20">Aktif</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-400 text-sm">-</span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-500 border border-gray-200">Nonaktif</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if(count($riwayats) > 0)
                            <div class="pt-3 mt-4 border-t border-gray-200">
                                <span class="block text-[10px] text-gray-400 mb-0.5">Terakhir diperbarui</span>
                                <div class="text-xs font-medium text-gray-700">
                                    {{ $riwayats->first()->dibuat_pada->translatedFormat('d F Y, H:i') }}
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
        </div>

        {{-- Riwayat Perubahan --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm shadow-gray-100/50 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/30 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Riwayat Perubahan</h2>
                    <p class="text-xs text-gray-500 mt-1">Daftar historis perubahan pengaturan transaksi.</p>
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
                                <div class="font-medium text-gray-900">{{ $riwayat->dibuat_pada->translatedFormat('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $riwayat->dibuat_pada->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-700">
                                <div>Biaya Layanan (Per Struk)</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div>
                                    @if(isset($riwayat->nilai_lama['layanan_aktif']) && $riwayat->nilai_lama['layanan_aktif'])
                                        Rp {{ number_format($riwayat->nilai_lama['nominal_layanan'] ?? 0, 0, ',', '.') }}
                                    @else
                                        Nonaktif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <div>
                                    @if(isset($riwayat->nilai_baru['layanan_aktif']) && $riwayat->nilai_baru['layanan_aktif'])
                                        Rp {{ number_format($riwayat->nilai_baru['nominal_layanan'] ?? 0, 0, ',', '.') }}
                                    @else
                                        Nonaktif
                                    @endif
                                </div>
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
                            <td colspan="5" class="px-6 py-10 text-center">
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
@endsection
