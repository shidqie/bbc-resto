@extends('layouts.pos')

@section('title', 'Manajemen Pengguna')

@section('content')
<div x-data="{
    activeTab: '{{ request('type', 'karyawan') === 'pelanggan' ? 'pelanggan' : 'karyawan' }}',
    showCreateModal: false,
    busy: false,

    toggleStatus(id, nama, aktif) {
        if (this.busy) return;
        const self = this;
        if (confirm(`${aktif ? 'Nonaktifkan' : 'Aktifkan'} akun ${nama}?`)) {
            self.busy = true;
            fetch('/users/' + id + '/toggle-status', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
            }).then(r => r.json()).then(data => {
                if (data.success) { location.reload(); }
                else { alert(data.message || 'Gagal mengubah status.'); }
            }).catch(() => alert('Terjadi kesalahan jaringan.')).finally(() => self.busy = false);
        }
    }
}" class="flex-1 bg-gray-50 text-gray-800 pb-10 w-full h-full flex flex-col">
    <div class="w-full p-6 space-y-5 flex flex-col flex-1 min-h-0">

    <!-- Header Area -->
    <x-ui.page-header title="Manajemen Pengguna" subtitle="Kelola data karyawan dan konsumen yang terdaftar di sistem." :breadcrumbs="['Manajemen Pengguna', 'Data Karyawan']">
        <x-slot:actions>
            <button @click="showCreateModal = true" class="bg-gray-900 hover:bg-gray-800 text-white font-medium py-2.5 px-5 rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />
                Tambah Karyawan
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <!-- Alert Messages -->
    <x-ui.alert />

    <!-- ================= TAB: DATA KARYAWAN ================= -->
    <div x-show="activeTab === 'karyawan'" x-cloak class="flex-1 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col min-h-0">

        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-start items-start md:items-center gap-3 bg-white">
            <form action="{{ route('users.index') }}" method="GET" class="w-full md:w-72">
                <input type="hidden" name="type" value="karyawan">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau nomor WhatsApp..." width="w-full" />
            </form>
            <x-select-input name="role" form="filter-form" :options="$roles->pluck('nama_peran', 'nama_peran')->toArray()" :selected="request('role')" placeholder="Semua Peran" :auto-submit="true" />
        </div>
        <form id="filter-form" action="{{ route('users.index') }}" method="GET" class="hidden">
            <input type="hidden" name="type" value="karyawan">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="role" value="{{ request('role') }}">
        </form>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-auto flex-1">
            <x-ui.table class="min-w-[800px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Nama Karyawan</th>
                    <th class="px-4 py-3.5 text-left">Email</th>
                    <th class="px-4 py-3.5 text-left">Nomor WhatsApp</th>
                    <th class="px-4 py-3.5 text-left">Peran</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengguna as $user)
                    <x-ui.table.row>
                        <td class="px-4 py-4 align-middle text-sm text-gray-500 font-medium">
                            {{ $pengguna->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="min-w-0">
                                <div class="font-medium text-gray-900 text-sm truncate">{{ $user->nama }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="text-sm text-gray-600">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="text-sm text-gray-600">{{ $user->nomor_telepon ? \App\Support\WhatsAppNumber::formatForDisplay($user->nomor_telepon) : '-' }}</div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            @if($user->peran)
                                <x-ui.badge color="primary" size="sm">{{ $user->peran->nama_peran }}</x-ui.badge>
                            @else
                                <x-ui.badge color="gray" size="sm">-</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('users.show', $user) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-sky-50 text-sky-600 hover:bg-sky-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus" class="w-7 h-7 rounded-full flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors">
                                        <x-heroicon-o-trash class="w-3 h-3" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="users" title="Belum ada data karyawan" message="Data akan muncul setelah karyawan ditambahkan." :colspan="6" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-white">
            {{ $pengguna->links() }}
        </div>
    </div>

    <!-- ================= TAB: DATA KONSUMEN ================= -->
    <div x-show="activeTab === 'pelanggan'" x-cloak class="flex-1 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col min-h-0">

        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-start items-start md:items-center gap-4 bg-white">
            <form action="{{ route('users.index') }}" method="GET" class="w-full md:w-72">
                <input type="hidden" name="type" value="pelanggan">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau nomor WhatsApp..." width="w-full" />
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-auto flex-1">
            <x-ui.table class="min-w-[800px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Nama Konsumen</th>
                    <th class="px-4 py-3.5 text-left">Email</th>
                    <th class="px-4 py-3.5 text-left">Nomor WhatsApp</th>
                    <th class="px-4 py-3.5 text-left">Alamat</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pelanggan as $user)
                    <x-ui.table.row>
                        <td class="px-4 py-4 align-middle text-sm text-gray-500 font-medium">
                            {{ $pelanggan->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="min-w-0">
                                <div class="font-medium text-gray-900 text-sm truncate">{{ $user->nama }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="text-sm text-gray-600 truncate max-w-xs">{{ $user->email ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="text-sm text-gray-600">{{ $user->nomor_telepon ? \App\Support\WhatsAppNumber::formatForDisplay($user->nomor_telepon) : '-' }}</div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="text-sm text-gray-600 truncate max-w-xs">
                                {{ $user->alamat ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('pelanggan.show', $user) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-sky-50 text-sky-600 hover:bg-sky-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                <form action="{{ route('pelanggan.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data konsumen ini? Semua riwayat pesanan juga akan terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.action-button type="submit" title="Hapus">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </x-ui.action-button>
                                </form>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="users" title="Belum ada data konsumen" message="Data akan muncul setelah konsumen ditambahkan." :colspan="6" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-white">
            {{ $pelanggan->links() }}
        </div>
    </div>

    <!-- ================= MODAL: TAMBAH KARYAWAN ================= -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showCreateModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Karyawan</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <x-input-wa name="nomor_telepon" label="Nomor WhatsApp" placeholder="08xxxxxxxxxx" :value="old('nomor_telepon')" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Peran</label>
                        <select name="peran_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <option value="">Pilih Peran</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->nama_peran }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="status_aktif" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-700">Akun Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection