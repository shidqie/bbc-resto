<x-layouts.landing>
    <x-slot:title>Profil Saya — Saung Babakan Cinta</x-slot:title>

    <section class="py-12 bg-canvas min-h-screen">
        <div class="max-w-2xl mx-auto px-4 lg:px-8">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-primary">Profil Saya</h1>
                    <p class="text-sm text-body/70 mt-1">Kelola data diri dan keamanan akun Anda.</p>
                </div>
                <a href="{{ route('konsumen.pesanan.index') }}" class="text-xs font-semibold text-primary hover:underline">
                    ← Kembali ke Pesanan
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Data Profil --}}
            <div class="bg-white rounded-xl border border-primary/10 p-6 mb-6">
                <h2 class="text-base font-bold text-primary mb-5 flex items-center gap-2">
                    <x-heroicon-o-user-circle class="w-5 h-5" /> Data Profil
                </h2>

                <form method="POST" action="{{ route('konsumen.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="nama" class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input id="nama" type="text" name="nama" value="{{ old('nama', $pelanggan->nama) }}"
                               required autocomplete="name"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary">
                        @error('nama')<p class="text-xs text-red-500 font-medium mt-1.5">{{ $message }}</p>@enderror
                    </div>

                    <x-input-wa name="nomor_telepon" label="Nomor WhatsApp" :value="$pelanggan->nomor_telepon" :required="true" />

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-gray-400 text-xs">(opsional)</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email', $pelanggan->email) }}"
                               autocomplete="email"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary">
                        @error('email')<p class="text-xs text-red-500 font-medium mt-1.5">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="alamat" class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat <span class="text-gray-400 text-xs">(opsional)</span></label>
                        <textarea id="alamat" name="alamat" rows="2" autocomplete="street-address"
                                  class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary">{{ old('alamat', $pelanggan->alamat) }}</textarea>
                        @error('alamat')<p class="text-xs text-red-500 font-medium mt-1.5">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 bg-primary hover:bg-primary-container text-white font-semibold text-sm rounded-xl transition-all duration-200 active:scale-[0.99]">
                        Simpan Profil
                    </button>
                </form>
            </div>

            {{-- Ganti Password --}}
            <div class="bg-white rounded-xl border border-primary/10 p-6">
                <h2 class="text-base font-bold text-primary mb-5 flex items-center gap-2">
                    <x-heroicon-o-lock-closed class="w-5 h-5" /> Ganti Password
                </h2>

                <form method="POST" action="{{ route('konsumen.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    @if($pelanggan->kata_sandi)
                        <div>
                            <label for="kata_sandi_saat_ini" class="block text-sm font-semibold text-gray-700 mb-1.5">Password Saat Ini <span class="text-red-500">*</span></label>
                            <input id="kata_sandi_saat_ini" type="password" name="kata_sandi_saat_ini" required autocomplete="current-password"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary">
                            @error('kata_sandi_saat_ini')<p class="text-xs text-red-500 font-medium mt-1.5">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <p class="text-xs text-amber-600 font-medium bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                            Akun Anda belum memiliki password (dibuat dari pesanan tanpa akun). Isi password baru untuk mengamankan akun.
                        </p>
                    @endif

                    <div>
                        <label for="kata_sandi" class="block text-sm font-semibold text-gray-700 mb-1.5">Password Baru <span class="text-red-500">*</span></label>
                        <input id="kata_sandi" type="password" name="kata_sandi" required minlength="8" autocomplete="new-password"
                               placeholder="Minimal 8 karakter"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary">
                        @error('kata_sandi')<p class="text-xs text-red-500 font-medium mt-1.5">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="kata_sandi_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Ulangi Password Baru <span class="text-red-500">*</span></label>
                        <input id="kata_sandi_confirmation" type="password" name="kata_sandi_confirmation" required minlength="8" autocomplete="new-password"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-primary">
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 bg-primary hover:bg-primary-container text-white font-semibold text-sm rounded-xl transition-all duration-200 active:scale-[0.99]">
                        Ganti Password
                    </button>
                </form>
            </div>

        </div>
    </section>
</x-layouts.landing>
