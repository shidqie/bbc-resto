@extends('layouts.pos')

@section('title', 'Pengaturan Transaksi')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Pengaturan Transaksi" subtitle="Kelola pajak dan biaya layanan yang diterapkan pada transaksi Dine-In." :breadcrumbs="['Pengaturan', 'Pajak & Layanan']">
        </x-ui.page-header>

        @if (session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-xl flex items-center gap-3">
            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
        @endif

        <div x-data="{
            pajakAktif: {{ old('pajak_aktif', $pengaturan->pajak_aktif ?? false) ? 'true' : 'false' }},
            persentasePajak: {{ old('persentase_pajak', (float) ($pengaturan->persentase_pajak ?? 0)) }},
            layananAktif: {{ old('layanan_aktif', $pengaturan->layanan_aktif ?? false) ? 'true' : 'false' }},
            persentaseLayanan: {{ old('persentase_layanan', (float) ($pengaturan->persentase_layanan ?? 0)) }},
            subtotal: 100000,
            get totalPajak() { return this.pajakAktif ? (this.subtotal * (this.persentasePajak / 100)) : 0; },
            get totalLayanan() { return this.layananAktif ? (this.subtotal * (this.persentaseLayanan / 100)) : 0; },
            get totalTagihan() { return this.subtotal + this.totalPajak + this.totalLayanan; }
        }">
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                {{-- Form Pengaturan --}}
                <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm shadow-gray-100/50">
                    <form action="{{ route('admin.pengaturan.transaksi.update') }}" method="POST">
                        @csrf
                        
                        <div class="space-y-8">
                            {{-- Pajak --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-1">
                                    <h2 class="text-base font-semibold text-gray-900">Pajak / PPN</h2>
                                    <p class="text-sm text-gray-500 mt-1">Input yang dikenakan pada pesanan Dine-In.</p>
                                </div>
                                <div class="md:col-span-1 space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="pajak_aktif" value="1" x-model="pajakAktif" class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700">Aktifkan Pajak</span>
                                    </label>
                                    
                                    <div x-show="pajakAktif" x-transition>
                                        <x-ui.input type="number" step="0.01" name="persentase_pajak" label="Persentase (%)" x-model.number="persentasePajak" :error="$errors->first('persentase_pajak')" />
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-50">

                            {{-- Biaya Layanan --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-1">
                                    <h2 class="text-base font-semibold text-gray-900">Biaya Layanan (Service Charge)</h2>
                                    <p class="text-sm text-gray-500 mt-1">Biaya layanan opsional yang dikenakan sebelum pajak.</p>
                                </div>
                                <div class="md:col-span-1 space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="layanan_aktif" value="1" x-model="layananAktif" class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700">Aktifkan Biaya Layanan</span>
                                    </label>
                                    
                                    <div x-show="layananAktif" x-transition>
                                        <x-ui.input type="number" step="0.01" name="persentase_layanan" label="Persentase (%)" x-model.number="persentaseLayanan" :error="$errors->first('persentase_layanan')" />
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
                            <x-heroicon-o-calculator class="w-5 h-5 text-blue-500" />
                            Simulasi Perhitungan
                        </h2>
                        
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-100 flex-1">
                            <p class="text-[11px] text-gray-500 mb-4 pb-3 border-b border-gray-200">Contoh transaksi Dine-In jika subtotal adalah Rp 100.000</p>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-semibold text-gray-900">Rp 100.000</span>
                                </div>
                                <div class="flex justify-between text-xs" x-show="layananAktif" x-transition>
                                    <span class="text-gray-600">Layanan (<span x-text="persentaseLayanan || 0"></span>%)</span>
                                    <span class="font-semibold text-gray-900" x-text="'Rp ' + totalLayanan.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between text-xs" x-show="pajakAktif" x-transition>
                                    <span class="text-gray-600">Pajak (<span x-text="persentasePajak || 0"></span>%)</span>
                                    <span class="font-semibold text-gray-900" x-text="'Rp ' + totalPajak.toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                            
                            <hr class="border-gray-200 my-4 border-dashed">
                            
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-bold text-gray-900">Total Tagihan</span>
                                <span class="font-black text-blue-600 text-base" x-text="'Rp ' + totalTagihan.toLocaleString('id-ID')"></span>
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
                            <p class="text-[11px] text-gray-500 mb-4 pb-3 border-b border-gray-200">Pengaturan yang sedang aktif di sistem.</p>
                            
                            <div class="space-y-4 flex-1">
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 mb-1">Pajak / PPN</span>
                                    @if($pengaturan->pajak_aktif ?? false)
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 text-sm">{{ (float) ($pengaturan->persentase_pajak ?? 0) }}%</span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-400 text-sm">-</span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-500 border border-gray-200">Nonaktif</span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 mb-1">Biaya Layanan</span>
                                    @if($pengaturan->layanan_aktif ?? false)
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 text-sm">{{ (float) ($pengaturan->persentase_layanan ?? 0) }}%</span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Aktif</span>
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
                                    {{ $riwayats->first()->dibuat_pada->format('d F Y, H:i') }}
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5">
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
                        <tr class="bg-white border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
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
                                <div>Pajak</div>
                                <div>Layanan</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div>{{ isset($riwayat->nilai_lama['pajak_aktif']) && $riwayat->nilai_lama['pajak_aktif'] ? ((float)($riwayat->nilai_lama['persentase_pajak'] ?? 0)) . '%' : 'Nonaktif' }}</div>
                                <div>{{ isset($riwayat->nilai_lama['layanan_aktif']) && $riwayat->nilai_lama['layanan_aktif'] ? ((float)($riwayat->nilai_lama['persentase_layanan'] ?? 0)) . '%' : 'Nonaktif' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <div>{{ isset($riwayat->nilai_baru['pajak_aktif']) && $riwayat->nilai_baru['pajak_aktif'] ? ((float)($riwayat->nilai_baru['persentase_pajak'] ?? 0)) . '%' : 'Nonaktif' }}</div>
                                <div>{{ isset($riwayat->nilai_baru['layanan_aktif']) && $riwayat->nilai_baru['layanan_aktif'] ? ((float)($riwayat->nilai_baru['persentase_layanan'] ?? 0)) . '%' : 'Nonaktif' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-[10px]">
                                        {{ strtoupper(substr($riwayat->diubahOleh->nama ?? 'S', 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-gray-700">{{ $riwayat->diubahOleh->nama ?? 'Sistem' }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center">
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
