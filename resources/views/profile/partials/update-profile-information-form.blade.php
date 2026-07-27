<section>
    <header class="pb-3 border-b border-gray-100">
        <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
            <i class="fa-solid fa-user-pen text-[#0F2E23]"></i> Informasi Profil
        </h2>
        <p class="mt-1 text-xs text-gray-500 font-medium">
            Perbarui data diri dan alamat email pengguna akun Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-solid fa-user text-gray-400 text-[11px]"></i> Nama Lengkap
            </label>
            <input id="name" name="name" type="text" 
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-900 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/20 transition-all outline-none" 
                value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-solid fa-envelope text-gray-400 text-[11px]"></i> Alamat Email
            </label>
            <input id="email" name="email" type="email" 
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-xs font-semibold text-gray-900 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/20 transition-all outline-none" 
                value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-xs text-amber-700 bg-amber-50 p-3 rounded-xl border border-amber-200">
                        Alamat email Anda belum terverifikasi.
                        <button form="send-verification" class="underline font-bold hover:text-amber-900 ml-1">
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-bold text-xs text-emerald-600">
                            Link verifikasi baru telah dikirimkan ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-[#0F2E23] hover:bg-[#163e30] text-white text-xs font-extrabold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                <i class="fa-solid fa-check text-xs"></i> Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-bold text-emerald-600 flex items-center gap-1"
                ><i class="fa-solid fa-circle-check"></i> Perubahan berhasil disimpan!</p>
            @endif
        </div>
    </form>
</section>
