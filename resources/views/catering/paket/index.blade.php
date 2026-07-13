<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Paket Catering & Nasi Box</h2>
                <a href="{{ route('paket-catering.create') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-plus mr-2"></i> Tambah Paket
                </a>
            </div>

            @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{{ session('success') }}</div>
            @endif

            <!-- Filter -->
            <div class="flex gap-2">
                <a href="{{ route('paket-catering.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $jenis === 'all' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">Semua</a>
                <a href="{{ route('paket-catering.index', ['jenis' => 'catering']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $jenis === 'catering' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">Catering</a>
                <a href="{{ route('paket-catering.index', ['jenis' => 'nasi_box']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $jenis === 'nasi_box' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">Nasi Box</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Paket</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga / Porsi</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Bahan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($pakets as $paket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $paket->nama_paket }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $paket->jenis_paket === 'catering' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $paket->jenis_paket === 'catering' ? 'Catering' : 'Nasi Box' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium">Rp {{ number_format($paket->harga, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500">{{ $paket->detail_bahan_count }} item</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('paket-catering.toggle', $paket) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $paket->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $paket->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right space-x-1">
                                    <a href="{{ route('paket-catering.show', $paket) }}" class="inline-block p-2 text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors"><i class="fa-solid fa-eye"></i></a>
                                    <a href="{{ route('paket-catering.edit', $paket) }}" class="inline-block p-2 text-yellow-600 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors"><i class="fa-solid fa-pencil"></i></a>
                                    <form action="{{ route('paket-catering.destroy', $paket) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus paket ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300 block"></i>
                                    Belum ada paket. Klik "Tambah Paket" untuk membuat yang pertama.
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
