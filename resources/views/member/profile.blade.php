<x-layouts.member>
    <x-slot:title>Profil Saya</x-slot:title>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-user text-primary text-xl"></i>
            <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl flex items-start gap-3">
                    <i class="fa-solid fa-circle-check mt-1"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form action="{{ route('member.profile.update') }}" method="POST" class="max-w-2xl space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors" required>
                    @error('name')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors" required>
                    @error('email')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors" required>
                    @error('phone_number')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 font-medium transition-colors shadow-sm shadow-primary/30">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.member>
