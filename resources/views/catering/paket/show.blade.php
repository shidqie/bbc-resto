<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Detail Paket: {{ $paketCatering->nama_paket }}</h2>
                <a href="{{ route('paket-catering.index') }}" class="text-sm text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                    <div><span class="text-sm text-gray-500">Jenis:</span> <span class="font-medium px-2.5 py-0.5 rounded-full text-xs {{ $paketCatering->jenis_paket === 'catering' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $paketCatering->jenis_paket === 'catering' ? 'Catering' : 'Nasi Box' }}</span></div>
                    <div><span class="text-sm text-gray-500">Harga / Porsi:</span> <span class="font-bold text-lg">Rp {{ number_format($paketCatering->harga, 0, ',', '.') }}</span></div>
                </div>
                @if($paketCatering->deskripsi)
                <p class="text-gray-600 text-sm">{{ $paketCatering->deskripsi }}</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Komposisi Bahan (BOM per 1 porsi)</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bahan Baku</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($paketCatering->detailBahan as $detail)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $detail->bahanBaku->nama_bahan }}</td>
                            <td class="px-4 py-3 text-right">{{ $detail->jumlah_kebutuhan }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $detail->bahanBaku->satuan->nama_satuan }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
