<section>
    <header class="pb-3 border-b border-neutral-100">
        <h2 class="text-base font-semibold text-neutral-900 flex items-center gap-2">
            <x-heroicon-o-key class="text-neutral-400 w-5 h-5" /> Ubah Kata Sandi
        </h2>
        <p class="mt-1 text-xs text-neutral-500 font-medium">
            Pastikan akun Anda menggunakan kata sandi yang aman dan tidak mudah ditebak.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-medium text-neutral-700 mb-1.5">
                Kata Sandi Saat Ini
            </label>
            <input id="update_password_current_password" name="current_password" type="password"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-white text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-900 transition-all outline-none"
                autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password" class="block text-xs font-medium text-neutral-700 mb-1.5">
                Kata Sandi Baru
            </label>
            <input id="update_password_password" name="password" type="password"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-white text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-900 transition-all outline-none"
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-medium text-neutral-700 mb-1.5">
                Konfirmasi Kata Sandi Baru
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-white text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-900 transition-all outline-none"
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-4 py-2.5 bg-neutral-900 hover:bg-neutral-700 text-white text-xs font-medium rounded-xl transition-colors flex items-center gap-2">
                Simpan Password Baru
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-medium text-neutral-700 flex items-center gap-1"
                ><x-heroicon-o-check-circle class="w-5 h-5" /> Password berhasil diperbarui!</p>
            @endif
        </div>
    </form>
</section>
