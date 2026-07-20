@extends('layouts.pos')

@section('title', 'Data Pengguna')

@section('content')
<div x-data="{ 
    showCreateModal: false, 
    showEditModal: false, 
    showDeleteModal: false, 
    editForm: { id: '', name: '', email: '', phone_number: '', role_id: '' },
    deleteForm: { id: '', name: '' }
}" class="p-4 md:p-8 w-full h-full flex flex-col bg-[#F3F4F6]">
    
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ $pageTitle }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $pageDescription }}</p>
        </div>
        @if($type !== 'pelanggan')
        <button @click="showCreateModal = true" class="bg-primary hover:bg-primary/90 text-white font-medium py-2.5 px-5 rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm">
            <x-heroicon-o-plus class="w-4 h-4" />
            Tambah Pengguna
        </button>
        @endif
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

    <!-- Desktop Table View (SaaS Style) & Mobile View Container -->
    <div class="flex-1 bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden flex flex-col">
        
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-start items-start md:items-center gap-4 bg-white">
            <form action="{{ route('users.index') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                <button type="submit" class="absolute left-3 top-2.5">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 hover:text-primary transition-colors" />
                </button>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-auto flex-1">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-[#F8FAFC] text-xs text-gray-600 font-semibold border-b border-gray-200">
                        <th class="px-6 py-4 w-12 text-center bg-[#F8FAFC]">No.</th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-gray-100 transition-colors group bg-[#F8FAFC]">
                            <div class="flex items-center gap-1">Nama Pengguna <x-heroicon-m-chevron-up-down class="w-4 h-4 text-gray-400 group-hover:text-gray-600" /></div>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-gray-100 transition-colors group bg-[#F8FAFC]">
                            <div class="flex items-center gap-1">Email <x-heroicon-m-chevron-up-down class="w-4 h-4 text-gray-400 group-hover:text-gray-600" /></div>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-gray-100 transition-colors group bg-[#F8FAFC]">
                            <div class="flex items-center gap-1 text-center">Nomor HP <x-heroicon-m-chevron-up-down class="w-4 h-4 text-gray-400 group-hover:text-gray-600" /></div>
                        </th>
                        @if($type !== 'pelanggan')
                        <th class="px-6 py-4 cursor-pointer hover:bg-gray-100 transition-colors group bg-[#F8FAFC]">
                            <div class="flex items-center gap-1 text-center">Role <x-heroicon-m-chevron-up-down class="w-4 h-4 text-gray-400 group-hover:text-gray-600" /></div>
                        </th>
                        @else
                        <th class="px-6 py-4 cursor-pointer hover:bg-gray-100 transition-colors group bg-[#F8FAFC]">
                            <div class="flex items-center gap-1 text-center">Bergabung Sejak <x-heroicon-m-chevron-up-down class="w-4 h-4 text-gray-400 group-hover:text-gray-600" /></div>
                        </th>
                        @endif
                        @if($type !== 'pelanggan')
                        <th class="px-6 py-4 text-center bg-[#F8FAFC]">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-center text-sm text-gray-500 font-medium">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 text-sm">{{ $user->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">{{ $user->phone_number ?? '-' }}</div>
                        </td>
                        @if($type !== 'pelanggan')
                        <td class="px-6 py-4">
                            @if($user->role && strtolower($user->role->name) === 'admin')
                                <span class="bg-rose-100 text-rose-700 py-1 px-3 rounded-full text-xs font-medium">{{ $user->role->name }}</span>
                            @elseif($user->role && strtolower($user->role->name) === 'manajer')
                                <span class="bg-amber-100 text-amber-700 py-1 px-3 rounded-full text-xs font-medium">{{ $user->role->name }}</span>
                            @else
                                <span class="bg-teal-100 text-teal-700 py-1 px-3 rounded-full text-xs font-medium">{{ $user->role ? $user->role->name : 'Kasir' }}</span>
                            @endif
                        </td>
                        @else
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-500">{{ $user->created_at->format('d M Y') }}</span>
                        </td>
                        @endif
                        @if($type !== 'pelanggan')
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-3">
                                <button @click="showEditModal = true; editForm = { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', phone_number: '{{ addslashes($user->phone_number) }}', role_id: '{{ $user->role_id }}' }" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                </button>
                                <button @click="showDeleteModal = true; deleteForm = { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}' }" class="text-gray-400 hover:text-rose-500 transition-colors" title="Hapus">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Belum ada data pengguna.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Hidden on Desktop) -->
        <div class="md:hidden flex-1 overflow-auto flex flex-col p-4 gap-4 bg-gray-50">
            @forelse($users as $user)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Card Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="font-bold text-gray-800 text-sm truncate pr-2">No. {{ $users->firstItem() + $loop->index }}</div>
                    
                    @if($type !== 'pelanggan')
                    <div class="flex items-center gap-2 shrink-0">
                        <button @click="showEditModal = true; editForm = { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', phone_number: '{{ addslashes($user->phone_number) }}', role_id: '{{ $user->role_id }}' }" class="p-1.5 text-blue-500 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md transition-colors">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                        </button>
                        <button @click="showDeleteModal = true; deleteForm = { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}' }" class="p-1.5 text-rose-500 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md transition-colors">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>
                    @endif
                </div>
                
                <!-- Card Body -->
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Nama</div>
                        <div class="text-gray-500">:</div>
                        <div class="font-medium text-gray-800 truncate">{{ $user->name }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Email</div>
                        <div class="text-gray-500">:</div>
                        <div class="text-gray-700 truncate">{{ $user->email }}</div>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Nomor HP</div>
                        <div class="text-gray-500">:</div>
                        <div class="text-gray-700">{{ $user->phone_number ?? '-' }}</div>
                    </div>
                    @if($type !== 'pelanggan')
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Role</div>
                        <div class="text-gray-500">:</div>
                        <div>
                            @if($user->role && strtolower($user->role->name) === 'admin')
                                <span class="bg-rose-100 text-rose-700 py-0.5 px-2 rounded-full text-[10px] font-medium">{{ $user->role->name }}</span>
                            @elseif($user->role && strtolower($user->role->name) === 'manajer')
                                <span class="bg-amber-100 text-amber-700 py-0.5 px-2 rounded-full text-[10px] font-medium">{{ $user->role->name }}</span>
                            @else
                                <span class="bg-teal-100 text-teal-700 py-0.5 px-2 rounded-full text-[10px] font-medium">{{ $user->role ? $user->role->name : 'Kasir' }}</span>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="grid grid-cols-[100px_10px_1fr] text-sm">
                        <div class="text-gray-500">Bergabung</div>
                        <div class="text-gray-500">:</div>
                        <div class="text-gray-700">{{ $user->created_at->format('d M Y') }}</div>
                    </div>
                    @endif
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
            {{ $users->links() }}
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
                                <input type="text" name="name" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                                <input type="text" name="phone_number" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role (Hak Akses)</label>
                                <select name="role_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option value="">Pilih Role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" name="password" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-xl">
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
                                <input type="text" name="name" x-model="editForm.name" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" x-model="editForm.email" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                                <input type="text" name="phone_number" x-model="editForm.phone_number" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role (Hak Akses)</label>
                                <select name="role_id" x-model="editForm.role_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option value="">Pilih Role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin diubah" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-xl">
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
                                        Apakah Anda yakin ingin menghapus pengguna <span class="font-bold text-gray-700" x-text="deleteForm.name"></span>? Data yang dihapus tidak dapat dikembalikan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-xl">
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
