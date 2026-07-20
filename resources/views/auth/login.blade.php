<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white mb-1">Masuk</h2>
        <p class="text-gray-400 text-sm">Silakan masuk menggunakan Email atau Nomor HP.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email or Phone -->
        <div>
            <label for="login" class="block font-medium text-sm text-gray-300 mb-2">Email atau Nomor HP</label>
            <x-ui.input id="login" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" placeholder="Contoh: admin@sbc.com atau 0812..." />
            <x-input-error :messages="$errors->get('login')" class="mt-2 text-danger" />
        </div>

        <!-- Password -->
        <div class="mt-5" x-data="{ show: false }">
            <label for="password" class="block font-medium text-sm text-gray-300 mb-2">Password</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" class="block w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white placeholder-gray-500 shadow-sm focus:border-primary focus:bg-gray-700 focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-sm sm:text-base">
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors focus:outline-none">
                    <i class="fa-solid fa-eye" x-show="!show"></i>
                    <i class="fa-solid fa-eye-slash" x-show="show" style="display: none;"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-5">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-700 bg-gray-800 text-primary shadow-sm focus:ring-primary focus:ring-offset-gray-900" name="remember">
                <span class="ms-2 text-sm text-gray-400">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-green-400 hover:text-green-300 transition-colors" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full py-3 px-4 bg-green-500 hover:bg-green-400 text-white font-semibold rounded-xl shadow-lg shadow-green-500/30 transition-all duration-300 flex justify-center items-center">
                Log In
            </button>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-400">
            Belum punya akun? 
            <a href="{{ route('register') }}" class="text-green-400 hover:text-green-300 transition-colors font-medium">Daftar sekarang</a>
        </div>
    </form>
</x-guest-layout>
