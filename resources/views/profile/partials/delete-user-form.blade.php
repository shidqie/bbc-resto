<section class="space-y-4">
    <header class="pb-3 border-b border-red-100">
        <h2 class="text-base font-extrabold text-red-700 flex items-center gap-2">
            <x-heroicon-o-exclamation-triangle class="text-red-600 w-5 h-5" /> Hapus Akun
        </h2>
        <p class="mt-1 text-xs text-gray-500 font-medium">
            Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Sebelum menghapus akun, pastikan Anda telah mengunduh data penting yang ingin disimpan.
        </p>
    </header>

    <button type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold rounded-xl shadow-md transition-all flex items-center gap-2"
    >
        <x-heroicon-o-trash class="w-3 h-3" /> Hapus Akun Saya
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-extrabold text-gray-900 flex items-center gap-2 mb-2">
                <x-heroicon-o-exclamation-triangle class="text-red-600 w-5 h-5" /> Apakah Anda yakin ingin menghapus akun?
            </h2>

            <p class="text-xs text-gray-500 font-medium leading-relaxed">
                Setelah akun dihapus, seluruh data dan akses Anda akan dihapus secara permanen. Masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda benar-benar ingin menghapus akun ini.
            </p>

            <div class="mt-4">
                <label for="password" class="sr-only">Kata Sandi</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-xs font-semibold text-gray-900 focus:bg-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20 outline-none transition-all"
                    placeholder="Masukkan Kata Sandi Anda"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors">
                    Batal
                </button>

                <button type="submit" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                    Hapus Akun Permanen
                </button>
            </div>
        </form>
    </x-modal>
</section>
