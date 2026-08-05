@extends('layouts.pos')

@section('title', 'Hak Akses Pengguna')

@section('content')
<div x-data="{ 
    showCreateModal: false, 
    showEditModal: false, 
    showDeleteModal: false, 
    editForm: { id: '', nama_peran: '' },
    deleteForm: { id: '', nama_peran: '' }
}" class="p-4 md:p-8 w-full h-full flex flex-col bg-[#F3F4F6]">
    
    <!-- Header Area -->
    <x-ui.page-header title="Hak Akses Pengguna" subtitle="Kelola peran (hak akses) pengguna di sistem." class="mb-6">
        <x-slot:actions>
            <button @click="showCreateModal = true" class="bg-primary hover:bg-primary/90 text-white font-medium py-2.5 px-5 rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm">
                <x-heroicon-o-plus class="w-4 h-4" />
                Tambah Hak Akses
            </button>
        </x-slot:actions>
    </x-ui.page-header>

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

    <!-- Table Container -->
    <div class="flex-1 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col">
        
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-start items-start md:items-center gap-4 bg-white">
            <form action="{{ route('roles.index') }}" method="GET" class="relative w-full md:w-72">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari hak akses..." />
                <button type="submit" class="absolute right-3 top-2.5">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 hover:text-primary transition-colors" />
                </button>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left w-12">No</th>
                    <th class="px-4 py-3 text-left">Nama Hak Akses</th>
                    <th class="px-4 py-3 text-left">Jumlah Pengguna</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($roles as $role)
                <tr class="hover:bg-gray-50/60 transition-colors group">
                    <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                        {{ $roles->firstItem() + $loop->index }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary"></span>
                            <span class="font-medium text-gray-900 text-sm">{{ $role->nama_peran }}</span>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $role->pengguna_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button @click="showEditModal = true; editForm = { id: '{{ $role->id }}', nama_peran: '{{ addslashes($role->nama_peran) }}' }" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                <x-heroicon-o-pencil-square class="w-3 h-3" />
                            </button>
                            <button @click="showDeleteModal = true; deleteForm = { id: '{{ $role->id }}', nama_peran: '{{ addslashes($role->nama_peran) }}' }" title="Hapus" class="w-7 h-7 rounded-full flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                <x-heroicon-o-trash class="w-3 h-3" />
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state icon="users" title="Belum ada data hak akses" message="Tambahkan hak akses baru menggunakan tombol di atas." :colspan="4" />
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-white">
            {{ $roles->links() }}
        </div>
    </div>

    <!-- Modal Tambah -->
    <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showCreateModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tambah Hak Akses Baru</h3>
                            <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-500">
                                <x-heroicon-o-x-mark class="w-5 h-5"/>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Hak Akses</label>
                                <input type="text" name="nama_peran" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
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
                <form :action="`/roles/${editForm.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Hak Akses</h3>
                            <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                                <x-heroicon-o-x-mark class="w-5 h-5"/>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Hak Akses</label>
                                <input type="text" name="nama_peran" x-model="editForm.nama_peran" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
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

    <!-- Modal Hapus -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showDeleteModal" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form :action="`/roles/${deleteForm.id}`" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Hapus Hak Akses</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus hak akses <span class="font-bold text-gray-700" x-text="deleteForm.nama_peran"></span>? Data yang dihapus tidak dapat dikembalikan.
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
