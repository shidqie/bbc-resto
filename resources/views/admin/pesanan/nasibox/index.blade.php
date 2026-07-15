<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pesanan Nasi Box') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <form method="GET" action="{{ route('admin.pesanan.nasibox.index') }}" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="menunggu_dp" {{ $status == 'menunggu_dp' ? 'selected' : '' }}>Menunggu DP</option>
                                <option value="menunggu_konfirmasi" {{ $status == 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                <option value="terkonfirmasi" {{ $status == 'terkonfirmasi' ? 'selected' : '' }}>Terkonfirmasi</option>
                                <option value="lunas" {{ $status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="dibatalkan" {{ $status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Kode Pesanan</th>
                                    <th scope="col" class="px-6 py-3">Nama Pemesan</th>
                                    <th scope="col" class="px-6 py-3">Varian Nasi Box</th>
                                    <th scope="col" class="px-6 py-3">Tgl Acara</th>
                                    <th scope="col" class="px-6 py-3">Total Tagihan</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pesanans as $pesanan)
                                    @php
                                        $h3 = \Carbon\Carbon::parse($pesanan->tanggal_acara)->subDays(3);
                                        $isWarning = in_array($pesanan->status, ['menunggu_dp', 'menunggu_konfirmasi']) && \Carbon\Carbon::today()->greaterThanOrEqualTo($h3);
                                    @endphp
                                    <tr class="border-b hover:bg-gray-50 {{ $isWarning ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $pesanan->kode_pesanan }}
                                            @if($isWarning)
                                                <span class="inline-flex items-center rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10 ml-2">URGENT</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ $pesanan->nama_pemesan }}</td>
                                        <td class="px-6 py-4">{{ $pesanan->menu->nama }} ({{ $pesanan->jumlah_box }} box)</td>
                                        <td class="px-6 py-4">{{ $pesanan->tanggal_acara->format('d M Y') }}</td>
                                        <td class="px-6 py-4">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusColors = [
                                                    'menunggu_dp' => 'bg-yellow-100 text-yellow-800',
                                                    'menunggu_konfirmasi' => 'bg-blue-100 text-blue-800',
                                                    'terkonfirmasi' => 'bg-green-100 text-green-800',
                                                    'lunas' => 'bg-green-200 text-green-900',
                                                    'dibatalkan' => 'bg-red-100 text-red-800',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 {{ $statusColors[$pesanan->status] ?? 'bg-gray-100' }} rounded-full text-xs font-semibold uppercase">
                                                {{ str_replace('_', ' ', $pesanan->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.pesanan.nasibox.show', $pesanan->id) }}" class="font-medium text-blue-600 hover:underline">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($pesanans->isEmpty())
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada pesanan ditemukan.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
