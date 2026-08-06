@extends('layouts.pos')

@section('title', 'Manajemen Pengguna')

@section('content')
<div x-data="{
    activeTab: '{{ request('type', 'karyawan') === 'pelanggan' ? 'pelanggan' : 'karyawan' }}',
    showCreateModal: false,
    showEditModal: false,
    editForm: { id: '', nama: '', email: '', nomor_telepon: '', peran_id: '', status_aktif: true },
    showCreatePelangganModal: false,
    showEditPelangganModal: false,
    pelangganForm: { id: '', nama: '', email: '', nomor_telepon: '', alamat: '' },
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
            <button x-show="activeTab === 'karyawan'" @click="showCreateModal = true" class="bg-gray-900 hover:bg-gray-800 text-white font-medium py-2.5 px-5 rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />
                Tambah Karyawan
            </button>
            <button x-show="activeTab === 'pelanggan'" @click="showCreatePelangganModal = true; pelangganForm = {id: '', nama: '', email: '', nomor_telepon: '', alamat: ''}" x-cloak class="bg-gray-900 hover:bg-gray-800 text-white font-medium py-2.5 px-5 rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />
                Tambah Konsumen
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
                                @if(!$user->status_aktif)
                                    <div class="text-xs text-red-500">Nonaktif</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="text-sm text-gray-600">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="text-sm text-gray-600">{{ $user->nomor_telepon ?? '-' }}</div>
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
                                <button type="button" @click="showEditModal = true; editForm = { id: '{{ $user->id }}', nama: '{{ addslashes($user->nama) }}', email: '{{ addslashes($user->email) }}', nomor_telepon: '{{ addslashes($user->nomor_telepon) }}', peran_id: '{{ $user->peran_id }}', status_aktif: {{ $user->status_aktif ? 'true' : 'false' }} }" title="Ubah" class="text-gray-500 transition hover:text-gray-900">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
                                <button type="button" @click="toggleStatus('{{ $user->id }}', '{{ addslashes($user->nama) }}', {{ $user->status_aktif ? 'true' : 'false' }})" :disabled="busy" :title="'{{ $user->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}'" class="text-gray-500 transition hover:text-gray-900">
                                    <x-heroicon-o-power class="w-4 h-4" />
                                </button>
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
                            <div class="text-sm text-gray-600">{{ $user->nomor_telepon ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4 align-middle">
                            <div class="text-sm text-gray-600 truncate max-w-xs">
                                {{ $user->alamat ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <x-ui.action-button onclick="openPelangganDrawer({{ $user->id }})" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </x-ui.action-button>
                                <button type="button" @click="showEditPelangganModal = true; pelangganForm = { id: {{ $user->id }}, nama: '{{ addslashes($user->nama) }}', email: '{{ addslashes($user->email ?? '') }}', nomor_telepon: '{{ addslashes($user->nomor_telepon ?? '') }}', alamat: '{{ addslashes($user->alamat ?? '') }}' }" title="Ubah" class="text-gray-500 transition hover:text-gray-900">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
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

    <!-- ================= MODAL: TAMBAH/EDIT KARYAWAN ================= -->
    <div x-show="showCreateModal || showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showCreateModal = false; showEditModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" x-text="showEditModal ? 'Edit Karyawan' : 'Tambah Karyawan'"></h3>
                <button @click="showCreateModal = false; showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form :action="showEditModal ? '/users/' + editForm.id : '{{ route('users.store') }}'" method="POST">
                @csrf
                <div x-show="showEditModal">
                    <input type="hidden" name="_method" value="PUT">
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" :value="showEditModal ? editForm.nama : ''" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" :value="showEditModal ? editForm.email : ''" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                        <input type="text" name="nomor_telepon" :value="showEditModal ? editForm.nomor_telepon : ''" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Peran</label>
                        <select name="peran_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <option value="">Pilih Peran</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" :selected="showEditModal && editForm.peran_id == '{{ $role->id }}'">{{ $role->nama_peran }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="!showEditModal">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                        <input type="password" name="password" :required="!showEditModal" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div x-show="!showEditModal">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_confirmation" :required="!showEditModal" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="status_aktif" value="1" :checked="showEditModal ? editForm.status_aktif : true" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-700">Akun Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showCreateModal = false; showEditModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: TAMBAH/EDIT PELANGGAN ================= -->
    <div x-show="showCreatePelangganModal || showEditPelangganModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showCreatePelangganModal = false; showEditPelangganModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" x-text="showEditPelangganModal ? 'Edit Konsumen' : 'Tambah Konsumen'"></h3>
                <button @click="showCreatePelangganModal = false; showEditPelangganModal = false" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form :action="showEditPelangganModal ? '/pelanggan/' + pelangganForm.id : '{{ route('pelanggan.store') }}'" method="POST">
                @csrf
                <div x-show="showEditPelangganModal">
                    <input type="hidden" name="_method" value="PUT">
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" :value="pelangganForm.nama" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" :value="pelangganForm.email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                        <input type="text" name="nomor_telepon" :value="pelangganForm.nomor_telepon" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                        <textarea name="alamat" x-model="pelangganForm.alamat" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showCreatePelangganModal = false; showEditPelangganModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: PELANGGAN DRAWER ================= -->
    <div id="drawerPelanggan" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closePelangganDrawer()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerPelangganPanel">
            
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                <div>
                    <h2 class="font-semibold text-gray-900">Detail Konsumen</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Informasi lengkap konsumen dan riwayat aktivitas.</p>
                </div>
                <button onclick="closePelangganDrawer()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Content --}}
            <div id="drawerPelangganContent" class="flex-1 overflow-y-auto">
                <!-- Data will be loaded via AJAX -->
            </div>
            
        </div>
    </div>

    </div>

</div>

<script>
    function openPelangganDrawer(id) {
        const drawer = document.getElementById('drawerPelanggan');
        const panel = document.getElementById('drawerPelangganPanel');
        const content = document.getElementById('drawerPelangganContent');
        
        // Show Loading
        content.innerHTML = '<div class="flex items-center justify-center h-32"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';
        
        drawer.classList.remove('hidden');
        drawer.style.display = 'flex';
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
        });

        fetch(`/pelanggan/${id}?ajax=1`)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
            });
    }

    function closePelangganDrawer() {
        const drawer = document.getElementById('drawerPelanggan');
        const panel = document.getElementById('drawerPelangganPanel');
        
        panel.classList.add('translate-x-full');
        setTimeout(() => {
            drawer.classList.add('hidden');
            drawer.style.display = 'none';
        }, 300);
    }
</script>
@endsection