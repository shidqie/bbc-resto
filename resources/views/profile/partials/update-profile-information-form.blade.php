<section>
    <header class="pb-3 border-b border-neutral-100">
        <h2 class="text-base font-semibold text-neutral-900 flex items-center gap-2">
            <x-heroicon-o-pencil class="text-neutral-400 w-5 h-5" /> Informasi Profil
        </h2>
        <p class="mt-1 text-xs text-neutral-500 font-medium">
            Perbarui data diri dan alamat email pengguna akun Anda.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-medium text-neutral-700 mb-1.5">
                Nama Lengkap
            </label>
            <input id="name" name="name" type="text"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-white text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-900 transition-all outline-none"
                value="{{ old('name', $user->nama) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-neutral-700 mb-1.5">
                Alamat Email
            </label>
            <input id="email" name="email" type="email"
                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-white text-sm text-neutral-900 placeholder-neutral-400 focus:border-neutral-900 transition-all outline-none"
                value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-4 py-2.5 bg-neutral-900 hover:bg-neutral-700 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-2">
                <x-heroicon-o-check class="w-3 h-3" /> Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-medium text-neutral-700 flex items-center gap-1"
                ><x-heroicon-o-check-circle class="w-5 h-5" /> Perubahan berhasil disimpan!</p>
            @endif
        </div>
    </form>
</section>
