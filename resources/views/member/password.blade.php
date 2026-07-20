<x-layouts.member>
    <x-slot:title>Ubah Password</x-slot:title>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-lock text-primary text-xl"></i>
            <h1 class="text-2xl font-bold text-gray-900">Ubah Password</h1>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl flex items-start gap-3">
                    <i class="fa-solid fa-circle-check mt-1"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <p class="text-gray-600 mb-6">Untuk keamanan akun Anda, mohon jangan bagikan password Anda kepada siapa pun.</p>

            <form action="{{ route('member.password.update') }}" method="POST" class="max-w-xl space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" id="current_password" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors" required>
                    @error('current_password')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" id="password" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors" required>
                    @error('password')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors" required>
                </div>

                <div class="pt-4">
                    <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 font-medium transition-colors shadow-sm shadow-primary/30">
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.member>
