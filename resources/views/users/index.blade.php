@extends('layouts.pos')

@section('title', 'Manajemen Pengguna')

@section('content')
<div x-data="{
    activeTab: '{{ request('type', 'pegawai') === 'pelanggan' ? 'pelanggan' : 'pegawai' }}',
    showCreateModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showResetModal: false,
    editForm: { id: '', nama: '', email: '', nomor_telepon: '', peran_id: '', status_aktif: true },
    deleteForm: { id: '', nama: '' },
    resetForm: { id: '', nama: '' },
    busy: false,

    toggleStatus(id, nama, aktif) {
        if (this.busy) return;
        const self = this;
        window.confirmDialog({
            title: 'Konfirmasi Status',
            name: (aktif ? 'Nonaktifkan' : 'Aktifkan') + ' akun ' + nama + '?',
            message: 'Anda yakin ingin mengubah status akun ini?',
            confirmText: (aktif ? 'Nonaktifkan' : 'Aktifkan'),
            cancelText: 'Batal',
            type: 'warning',
            onConfirm: function () {
                self.busy = true;
                fetch('/users/' + id + '/toggle-status', {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data.success) { location.reload(); }
                    else { window.showToast('error', data.message || 'Gagal mengubah status.'); }
                }).catch(() => window.showToast('error', 'Terjadi kesalahan jaringan.')).finally(() => self.busy = false);
            }
        });
    },

    resetPassword() {
        const form = document.getElementById('resetPasswordForm');
        const fd = new FormData(form);
        fetch('/users/' + this.resetForm.id + '/reset-password', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            body: fd
        }).then(r => r.json().then(d => ({ ok: r.ok, d }))).then(({ ok, d }) => {
            if (ok && d.success) { this.showResetModal = false; location.reload(); }
            else { window.showToast('error', d.message || (d.errors ? Object.values(d.errors).flat().join('\n') : 'Gagal mengatur ulang kata sandi.')); }
        }).catch(() => window.showToast('error', 'Terjadi kesalahan jaringan.'));
    }
}" class="p-4 md:p-8 w-full h-full flex flex-col bg-[#F3F4F6]">

    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola pengguna internal (staf, kasir, admin) dan pelanggan yang terdaftar di sistem.</p>
        </div>
        <button x-show="activeTab === 'pegawai'" @click="showCreateModal = true" class="bg-primary hover:bg-primary/90 text-white font-medium py-2.5 px-5 rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm">
            <x-heroicon-o-plus class="w-4 h-4" />
            Tambah Pengguna
        </button>
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-6 border-b border-gray-200 mb-5">
        <button type="button" @click="activeTab = 'pegawai'" class="py-3 text-sm font-medium border-b-2 transition-colors" :class="activeTab === 'pegawai' ? 'border-gray-900 text-gray-900 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
            Data Pengguna
        </button>
        <button type="button" @click="activeTab = 'pelanggan'" class="py-3 text-sm font-medium border-b-2 transition-colors" :class="activeTab === 'pelanggan' ? 'border-gray-900 text-gray-900 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
            Data Pelanggan
        </button>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative flex items-center gap-2 text-sm" role="alert">
            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500"/>
            <span class="block sm:inline font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative text-sm" role="alert">
            <div class="flex items-center gap-2 font-medium mb-1">
                <x-heroicon-o-x-circle class="w-5 h-5 text-red-500"/>
                <span>Gagal menyimpan data:</span>
            </div>
            <ul class="list-disc list-inside ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ================= TAB: DATA PENGGUNA ================= -->
    <div x-show="activeTab === 'pegawai'" x-cloak class="flex-1 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col min-h-0">

        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-start items-start md:items-center gap-3 bg-white">
            <form action="{{ route('users.index') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="type" value="pegawai">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau nomor HP..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                <button type="submit" class="absolute left-3 top-2.5">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 hover:text-primary transition-colors" />
                </button>
            </form>
            <select name="role" form="filter-form" onchange="this.form.submit()" class="border border-gray-200 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->nama_peran }}" {{ request('role') === $role->nama_peran ? 'selected' : '' }}>{{ $role->nama_peran }}</option>
                @endforeach
            </select>
            <select name="status" form="filter-form" onchange="this.form.submit()" class="border border-gray-200 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <form id="filter-form" action="{{ route('users.index') }}" method="GET" class="hidden">
            <input type="hidden" name="type" value="pegawai">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="role" value="{{ request('role') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
        </form>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-auto flex-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Nama Pengguna</th>
                        <th class="px-4 py-3 text-left">Nomor HP</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Terakhir Masuk</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengguna as $user)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                            {{ $pengguna->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->nama, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 text-sm truncate">{{ $user->nama }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-600">{{ $user->nomor_telepon ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($user->peran)
                                <span class="bg-rose-50 text-rose-700 border border-rose-100 py-1 px-3 rounded-full text-xs font-medium">{{ $user->peran->nama_peran }}</span>
                            @else
                                <span class="bg-gray-50 text-gray-500 py-1 px-3 rounded-full text-xs font-medium">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->status_aktif)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-100 py-1 px-3 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 border border-gray-200 py-1 px-3 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-500">{{ $user->terakhir_masuk ? $user->terakhir_masuk->format('d M Y H:i') : 'Belum pernah masuk' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('users.show', $user) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-sky-50 text-sky-600 hover:bg-sky-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                <button @click="showEditModal = true; editForm = { id: '{{ $user->id }}', nama: '{{ addslashes($user->nama) }}', email: '{{ addslashes($user->email) }}', nomor_telepon: '{{ addslashes($user->nomor_telepon) }}', peran_id: '{{ $user->peran_id }}', status_aktif: {{ $user->status_aktif ? 'true' : 'false' }} }" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-3 h-3" />
                                </button>
                                <button @click="showResetModal = true; resetForm = { id: '{{ $user->id }}', nama: '{{ addslashes($user->nama) }}' }" title="Atur Ulang Kata Sandi" class="w-7 h-7 rounded-full flex items-center justify-center bg-violet-50 text-violet-600 hover:bg-violet-100 transition-colors">
                                    <x-heroicon-o-key class="w-3 h-3" />
                                </button>
                                <button @click="toggleStatus('{{ $user->id }}', '{{ addslashes($user->nama) }}', {{ $user->status_aktif ? 'true' : 'false' }})" :disabled="busy" :title="'{{ $user->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}'"
                                    class="w-7 h-7 rounded-full flex items-center justify-center transition-colors {{ $user->status_aktif ? 'bg-gray-100 text-gray-500 hover:bg-gray-200' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                    <x-heroicon-o-power class="w-3 h-3" />
                                </button>
                                <button @click="showDeleteModal = true; deleteForm = { id: '{{ $user->id }}', nama: '{{ addslashes($user->nama) }}' }" title="Hapus" class="w-7 h-7 rounded-full flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                    <x-heroicon-o-trash class="w-3 h-3" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            Belum ada data pengguna.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden flex-1 overflow-auto flex flex-col p-4 gap-4 bg-gray-50">
            @forelse($pengguna as $user)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="font-bold text-gray-800 text-sm truncate pr-2">No. {{ $pengguna->firstItem() + $loop->index }}</div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('users.show', $user) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-sky-50 text-sky-600 hover:bg-sky-100 transition-colors">
                            <x-heroicon-o-eye class="w-3 h-3" />
                        </a>
                        <button @click="showEditModal = true; editForm = { id: '{{ $user->id }}', nama: '{{ addslashes($user->nama) }}', email: '{{ addslashes($user->email) }}', nomor_telepon: '{{ addslashes($user->nomor_telepon) }}', peran_id: '{{ $user->peran_id }}', status_aktif: {{ $user->status_aktif ? 'true' : 'false' }} }" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                            <x-heroicon-o-pencil-square class="w-3 h-3" />
                        </button>
                        <button @click="showDeleteModal = true; deleteForm = { id: '{{ $user->id }}', nama: '{{ addslashes($user->nama) }}' }" title="Hapus" class="w-7 h-7 rounded-full flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                            <x-heroicon-o-trash class="w-3 h-3" />
                        </button>
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Nama</div>
                        <div class="text-gray-500">:</div>
                        <div class="font-medium text-gray-800 truncate">{{ $user->nama }} <span class="text-gray-400">({{ $user->email }})</span></div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Nomor HP</div>
                        <div class="text-gray-500">:</div>
                        <div class="text-gray-700">{{ $user->nomor_telepon ?? '-' }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Role</div>
                        <div class="text-gray-500">:</div>
                        <div>
                            <span class="bg-rose-50 text-rose-700 border border-rose-100 py-0.5 px-2 rounded-full text-xs font-medium">{{ $user->peran->nama_peran ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Status</div>
                        <div class="text-gray-500">:</div>
                        <div>
                            @if($user->status_aktif)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-100 py-0.5 px-2 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 border border-gray-200 py-0.5 px-2 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
                Belum ada data pengguna.
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-white">
            {{ $pengguna->links() }}
        </div>
    </div>

    <!-- ================= TAB: DATA PELANGGAN ================= -->
    <div x-show="activeTab === 'pelanggan'" x-cloak class="flex-1 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col min-h-0">

        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-start items-start md:items-center gap-4 bg-white">
            <form action="{{ route('users.index') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="type" value="pelanggan">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau nomor HP..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                <button type="submit" class="absolute left-3 top-2.5">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 hover:text-primary transition-colors" />
                </button>
            </form>
            <select name="status" form="filter-form-pelanggan" onchange="this.form.submit()" class="border border-gray-200 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <form id="filter-form-pelanggan" action="{{ route('users.index') }}" method="GET" class="hidden">
            <input type="hidden" name="type" value="pelanggan">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
        </form>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-auto flex-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Nama Pelanggan</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Nomor HP</th>
                        <th class="px-4 py-3 text-left">Alamat</th>
                        <th class="px-4 py-3 text-left">Tgl Pendaftaran</th>
                        <th class="px-4 py-3 text-center">Jml Pesanan</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pelanggan as $user)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                            {{ $pelanggan->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->nama, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 text-sm truncate">{{ $user->nama }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $user->email ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-600">{{ $user->nomor_telepon ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-600 truncate max-w-xs">{{ $user->pelanggan ? ($user->pelanggan->alamat ?? '-') : ($user->alamat ?? '-') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-500">{{ $user->dibuat_pada->format('d M Y') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-medium text-gray-900">{{ $user->pelanggan ? $user->pelanggan->pesanan()->count() : 0 }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($user->status_aktif)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-100 py-1 px-3 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 border border-gray-200 py-1 px-3 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('users.show', $user) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-sky-50 text-sky-600 hover:bg-sky-100 transition-colors">
                                    <x-heroicon-o-eye class="w-3 h-3" />
                                </a>
                                <button @click="toggleStatus('{{ $user->id }}', '{{ addslashes($user->nama) }}', {{ $user->status_aktif ? 'true' : 'false' }})" :disabled="busy" :title="'{{ $user->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    class="w-7 h-7 rounded-full flex items-center justify-center transition-colors {{ $user->status_aktif ? 'bg-gray-100 text-gray-500 hover:bg-gray-200' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                    <x-heroicon-o-power class="w-3 h-3" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                            Belum ada data pelanggan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden flex-1 overflow-auto flex flex-col p-4 gap-4 bg-gray-50">
            @forelse($pelanggan as $user)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="font-bold text-gray-800 text-sm truncate pr-2">No. {{ $pelanggan->firstItem() + $loop->index }}</div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('users.show', $user) }}" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-sky-50 text-sky-600 hover:bg-sky-100 transition-colors">
                            <x-heroicon-o-eye class="w-3 h-3" />
                        </a>
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Nama</div>
                        <div class="text-gray-500">:</div>
                        <div class="font-medium text-gray-800 truncate">{{ $user->nama }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Email</div>
                        <div class="text-gray-500">:</div>
                        <div class="text-gray-700 truncate">{{ $user->email }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Nomor HP</div>
                        <div class="text-gray-500">:</div>
                        <div class="text-gray-700">{{ $user->nomor_telepon ?? '-' }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Alamat</div>
                        <div class="text-gray-500">:</div>
                        <div class="text-gray-700 truncate">{{ $user->pelanggan ? ($user->pelanggan->alamat ?? '-') : ($user->alamat ?? '-') }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Bergabung</div>
                        <div class="text-gray-500">:</div>
                        <div class="text-gray-700">{{ $user->dibuat_pada->format('d M Y') }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Jml Pesanan</div>
                        <div class="text-gray-500">:</div>
                        <div class="font-medium text-gray-900">{{ $user->pelanggan ? $user->pelanggan->pesanan()->count() : 0 }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Status</div>
                        <div class="text-gray-500">:</div>
                        <div>
                            @if($user->status_aktif)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-100 py-0.5 px-2 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 border border-gray-200 py-0.5 px-2 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
                Belum ada data pelanggan.
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-white">
            {{ $pelanggan->links() }}
        </div>
    </div>

    <!-- Modal Tambah -->
    <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showCreateModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tambah Pengguna Baru</h3>
                            <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-500">
                                <x-heroicon-o-x-mark class="w-5 h-5"/>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                                <input type="text" name="nomor_telepon" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role (Hak Akses)</label>
                                <select name="peran_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option value="">Pilih Role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->nama_peran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" name="password" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
                                <select name="status_aktif" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-[2rem]">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Data
                        </button>
                        <button type="button" @click="showCreateModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showEditModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showEditModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form :action="`/users/${editForm.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Pengguna</h3>
                            <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                                <x-heroicon-o-x-mark class="w-5 h-5"/>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama" x-model="editForm.nama" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" x-model="editForm.email" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                                <input type="text" name="nomor_telepon" x-model="editForm.nomor_telepon" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role (Hak Akses)</label>
                                <select name="peran_id" x-model="editForm.peran_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option value="">Pilih Role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->nama_peran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
                                <select name="status_aktif" x-model.number="editForm.status_aktif" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option :value="1">Aktif</option>
                                    <option :value="0">Nonaktif</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin diubah" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-[2rem]">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Perbarui Data
                        </button>
                        <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div x-show="showResetModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showResetModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showResetModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showResetModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form id="resetPasswordForm" @submit.prevent="resetPassword()">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Atur Ulang Kata Sandi</h3>
                            <button type="button" @click="showResetModal = false" class="text-gray-400 hover:text-gray-500">
                                <x-heroicon-o-x-mark class="w-5 h-5"/>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">
                            Mengatur ulang kata sandi untuk akun <span class="font-bold text-gray-700" x-text="resetForm.nama"></span>.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                                <input type="password" name="password" required minlength="8" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" required minlength="8" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-[2rem]">
                        <button type="submit" :disabled="busy" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-violet-600 text-base font-medium text-white hover:bg-violet-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Kata Sandi
                        </button>
                        <button type="button" @click="showResetModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showDeleteModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form :action="`/users/${deleteForm.id}`" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Hapus Pengguna</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus pengguna <span class="font-bold text-gray-700" x-text="deleteForm.nama"></span>? Data yang dihapus tidak dapat dikembalikan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-[2rem]">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus
                        </button>
                        <button type="button" @click="showDeleteModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
