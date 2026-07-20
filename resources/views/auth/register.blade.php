<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white mb-1">Daftar Akun Baru</h2>
        <p class="text-gray-400 text-sm">Lengkapi data berikut untuk bergabung dengan Saung Babakan Cinta.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block font-medium text-sm text-gray-300 mb-2">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Misal: Budi Santoso" class="block w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white placeholder-gray-500 shadow-sm focus:border-primary focus:bg-gray-700 focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-sm sm:text-base">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger" />
        </div>

        <!-- Email Address -->
        <div class="mt-5">
            <label for="email" class="block font-medium text-sm text-gray-300 mb-2">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Misal: budi@sbc.com" class="block w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white placeholder-gray-500 shadow-sm focus:border-primary focus:bg-gray-700 focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-sm sm:text-base">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />
        </div>

        <!-- Phone Number -->
        <div class="mt-5">
            <label for="phone_number" class="block font-medium text-sm text-gray-300 mb-2">Nomor HP / WhatsApp</label>
            <input id="phone_number" type="text" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel" placeholder="Misal: 08123456789" class="block w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white placeholder-gray-500 shadow-sm focus:border-primary focus:bg-gray-700 focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-sm sm:text-base">
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2 text-danger" />
        </div>

        <!-- Password -->
        <div class="mt-5" x-data="{ show: false }">
            <label for="password" class="block font-medium text-sm text-gray-300 mb-2">Password</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" placeholder="••••••••" class="block w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white placeholder-gray-500 shadow-sm focus:border-primary focus:bg-gray-700 focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-sm sm:text-base">
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors focus:outline-none">
                    <i class="fa-solid fa-eye" x-show="!show"></i>
                    <i class="fa-solid fa-eye-slash" x-show="show" style="display: none;"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-5" x-data="{ show: false }">
            <label for="password_confirmation" class="block font-medium text-sm text-gray-300 mb-2">Konfirmasi Password</label>
            <div class="relative">
                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="block w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white placeholder-gray-500 shadow-sm focus:border-primary focus:bg-gray-700 focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-sm sm:text-base">
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors focus:outline-none">
                    <i class="fa-solid fa-eye" x-show="!show"></i>
                    <i class="fa-solid fa-eye-slash" x-show="show" style="display: none;"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-danger" />
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full py-3 px-4 bg-green-500 hover:bg-green-400 text-white font-semibold rounded-xl shadow-lg shadow-green-500/30 transition-all duration-300 flex justify-center items-center">
                Daftar Sekarang
            </button>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-400">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="text-green-400 hover:text-green-300 transition-colors font-medium">Log In di sini</a>
        </div>
    </form>
</x-guest-layout>
