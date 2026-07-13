<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <h2 class="text-2xl font-bold text-gray-900">Pesanan Catering & Nasi Box</h2>

            @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">{{ session('error') }}</div>
            @endif

            <!-- Filter Status -->
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('pesanan-catering.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'all' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">Semua</a>
                <a href="{{ route('pesanan-catering.index', ['status' => 'menunggu_konfirmasi']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'menunggu_konfirmasi' ? 'bg-yellow-500 text-white' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' }} transition-colors">Menunggu</a>
                <a href="{{ route('pesanan-catering.index', ['status' => 'terkonfirmasi']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'terkonfirmasi' ? 'bg-blue-500 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }} transition-colors">Terkonfirmasi</a>
                <a href="{{ route('pesanan-catering.index', ['status' => 'lunas']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'lunas' ? 'bg-green-500 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100' }} transition-colors">Lunas</a>
                <a href="{{ route('pesanan-catering.index', ['status' => 'selesai']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'selesai' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">Selesai</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pesanan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemesan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paket</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Porsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl. Acara</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($pesanans as $pesanan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-indigo-600">{{ $pesanan->no_pesanan }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium">{{ $pesanan->nama_pemesan }}</div>
                                    <div class="text-gray-500 text-xs">{{ $pesanan->no_telepon }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $pesanan->paketCatering->nama_paket }}</td>
                                <td class="px-6 py-4 text-center text-sm">{{ $pesanan->jumlah_porsi }}</td>
                                <td class="px-6 py-4 text-sm">{{ $pesanan->tanggal_acara->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusColors = [
                                            'menunggu_konfirmasi' => 'bg-yellow-100 text-yellow-800',
                                            'terkonfirmasi' => 'bg-blue-100 text-blue-800',
                                            'lunas' => 'bg-green-100 text-green-800',
                                            'dibatalkan' => 'bg-red-100 text-red-800',
                                            'selesai' => 'bg-gray-100 text-gray-800',
                                        ];
                                        $statusLabels = [
                                            'menunggu_konfirmasi' => 'Menunggu',
                                            'terkonfirmasi' => 'Terkonfirmasi',
                                            'lunas' => 'Lunas',
                                            'dibatalkan' => 'Dibatalkan',
                                            'selesai' => 'Selesai',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$pesanan->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabels[$pesanan->status] ?? $pesanan->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('pesanan-catering.show', $pesanan) }}" class="inline-block p-2 text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors"><i class="fa-solid fa-eye"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300 block"></i>
                                    Belum ada pesanan catering.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
