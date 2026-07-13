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
        <div class="mt-5">
            <label for="password" class="block font-medium text-sm text-gray-300 mb-2">Password</label>
            <x-ui.input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-5">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-700 bg-gray-800 text-primary shadow-sm focus:ring-primary focus:ring-offset-gray-900" name="remember">
                <span class="ms-2 text-sm text-gray-400">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-primary hover:text-white transition-colors" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <div class="mt-8">
            <x-ui.button type="submit" class="w-full py-3 text-base justify-center">
                Log In
            </x-ui.button>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-400">
            Belum punya akun? 
            <a href="{{ route('register') }}" class="text-primary hover:text-white transition-colors font-medium">Daftar sekarang</a>
        </div>
    </form>
</x-guest-layout>
