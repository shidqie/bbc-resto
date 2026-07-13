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
            <x-ui.input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Misal: Budi Santoso" class="bg-gray-800 border-gray-700 text-white placeholder-gray-500 focus:bg-gray-700" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger" />
        </div>

        <!-- Email Address -->
        <div class="mt-5">
            <label for="email" class="block font-medium text-sm text-gray-300 mb-2">Email</label>
            <x-ui.input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Misal: budi@sbc.com" class="bg-gray-800 border-gray-700 text-white placeholder-gray-500 focus:bg-gray-700" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />
        </div>

        <!-- Phone Number -->
        <div class="mt-5">
            <label for="phone_number" class="block font-medium text-sm text-gray-300 mb-2">Nomor HP / WhatsApp</label>
            <x-ui.input id="phone_number" type="text" name="phone_number" :value="old('phone_number')" required autocomplete="tel" placeholder="Misal: 08123456789" class="bg-gray-800 border-gray-700 text-white placeholder-gray-500 focus:bg-gray-700" />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2 text-danger" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <label for="password" class="block font-medium text-sm text-gray-300 mb-2">Password</label>
            <x-ui.input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" class="bg-gray-800 border-gray-700 text-white placeholder-gray-500 focus:bg-gray-700" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-5">
            <label for="password_confirmation" class="block font-medium text-sm text-gray-300 mb-2">Konfirmasi Password</label>
            <x-ui.input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="bg-gray-800 border-gray-700 text-white placeholder-gray-500 focus:bg-gray-700" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-danger" />
        </div>

        <div class="mt-8">
            <x-ui.button type="submit" class="w-full py-3 text-base justify-center">
                Daftar Sekarang
            </x-ui.button>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-400">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="text-primary hover:text-white transition-colors font-medium">Log In di sini</a>
        </div>
    </form>
</x-guest-layout>
