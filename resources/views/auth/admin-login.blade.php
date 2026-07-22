<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-[#111827] mb-2 tracking-tight">Portal Internal</h2>
        <p class="text-gray-500 text-sm">Masuk menggunakan akun Staff/Admin Anda.</p>
    </div>

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf

        <!-- Email or Phone -->
        <div>
            <x-ui.input id="login" label="Email atau Nomor HP" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" placeholder="Contoh: admin@bbc.com atau 0812..." :error="$errors->first('login')" />
        </div>

        <!-- Password -->
        <div class="mt-5" x-data="{ show: false }">
            <label for="password" class="block text-sm font-semibold text-gray-700 font-sans mb-1.5">
                Password
            </label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 transition-all duration-300 outline-none text-sm sm:text-base pr-12 {{ $errors->has('password') ? 'border-danger focus:border-danger focus:ring-danger/20' : '' }}">
                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors focus:outline-none flex items-center justify-center w-6 h-6">
                    <i class="fa-solid fa-eye" x-show="!show"></i>
                    <i class="fa-solid fa-eye-slash" x-show="show" style="display: none;"></i>
                </button>
            </div>
            @if($errors->has('password'))
                <p class="text-xs font-medium text-danger mt-1">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-5">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 bg-white text-primary shadow-sm focus:ring-primary focus:ring-offset-white w-4 h-4 transition-all" name="remember">
                <span class="ms-2 text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-primary hover:text-primary-container transition-colors" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <div class="mt-8">
            <x-ui.button type="submit" variant="primary" class="w-full py-3.5 shadow-lg shadow-primary/20">
                Log In Internal
            </x-ui.button>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-100 text-center text-sm text-gray-500">
            Bukan Karyawan? 
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-primary-container transition-colors">Portal Customer</a>
        </div>
    </form>
</x-guest-layout>
