<section>
    <header class="pb-3 border-b border-gray-100">
        <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
            <i class="fa-solid fa-key text-amber-600"></i> Ubah Kata Sandi
        </h2>
        <p class="mt-1 text-xs text-gray-500 font-medium">
            Pastikan akun Anda menggunakan kata sandi yang aman dan tidak mudah ditebak.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-solid fa-lock text-gray-400 text-[11px]"></i> Kata Sandi Saat Ini
            </label>
            <input id="update_password_current_password" name="current_password" type="password" 
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-900 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/20 transition-all outline-none" 
                autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password" class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-solid fa-key text-gray-400 text-[11px]"></i> Kata Sandi Baru
            </label>
            <input id="update_password_password" name="password" type="password" 
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-900 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/20 transition-all outline-none" 
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-gray-400 text-[11px]"></i> Konfirmasi Kata Sandi Baru
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-900 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/20 transition-all outline-none" 
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-[#0F2E23] hover:bg-[#163e30] text-white text-xs font-extrabold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i> Simpan Password Baru
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-bold text-emerald-600 flex items-center gap-1"
                ><i class="fa-solid fa-circle-check"></i> Password berhasil diperbarui!</p>
            @endif
        </div>
    </form>
</section>
