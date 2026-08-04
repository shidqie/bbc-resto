{{--
|--------------------------------------------------------------------------
| Admin Topbar Header (Minimalist)
|--------------------------------------------------------------------------
| Area kanan: tombol profil dropdown. Area kiri: sapaan role (dashboard/POS).
|--}}

<header class="no-print sticky top-0 h-16 bg-white border-b border-neutral-200 px-6 flex items-center justify-between shrink-0 z-[100]">
    {{-- Area Kiri: Halo Role Greeting (Hanya Muncul di Dashboard & Point of Sale) --}}
    <div class="flex items-center gap-3">
        @if(request()->routeIs('dashboard') || request()->routeIs('pos.*'))
            @php
                $roleName = auth()->user()->peran->nama_peran ?? 'Admin';
            @endphp
            <h2 class="text-base font-semibold text-neutral-900 flex items-center gap-2 tracking-tight">
                <x-heroicon-o-shield-check class="text-[#0D3024] w-4 h-4" /> Halo, <span class="text-[#0D3024]">{{ $roleName }}</span>
            </h2>
        @endif
    </div>

    <div class="flex items-center gap-3">
        {{-- Profile --}}
        <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
            <button @click="userMenuOpen = !userMenuOpen"
                    class="flex items-center gap-2 p-1 pl-3 bg-white border border-neutral-200 rounded-lg transition-colors hover:border-emerald-200 hover:bg-emerald-50/50 focus:outline-none">
                <span class="text-sm font-medium text-neutral-700 hidden md:inline truncate max-w-[120px]">{{ auth()->user()->nama ?? 'Admin' }}</span>
                <div class="w-8 h-8 rounded-full bg-[#0D3024] text-[#D4A843] flex items-center justify-center font-bold text-sm shrink-0">
                    {{ substr(auth()->user()->nama ?? 'A', 0, 1) }}
                </div>
            </button>

            {{-- Dropdown Profile --}}
            <div x-show="userMenuOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                 class="absolute right-0 mt-2 w-56 rounded-xl bg-white border border-neutral-200 p-1.5 z-50"
                 style="display: none;">

                {{-- Header Profile Info --}}
                <div class="px-3 py-2 border-b border-neutral-100 mb-1">
                    <p class="text-sm font-semibold text-neutral-900 truncate">{{ auth()->user()->nama ?? 'User' }}</p>
                    <p class="text-xs text-neutral-500 truncate">{{ auth()->user()->peran->nama_peran ?? 'Admin' }}</p>
                </div>

                {{-- My Profile Group --}}
                <div class="py-1">
                    <p class="px-3 text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">My Profile</p>

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900 transition-colors">
                        <x-heroicon-o-pencil class="text-neutral-400 w-4 h-4" />
                        <span>Edit Profile</span>
                    </a>

                    <a href="{{ route('profile.edit') }}#update-password" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900 transition-colors">
                        <x-heroicon-o-key class="text-neutral-400 w-4 h-4" />
                        <span>Ubah Password</span>
                    </a>
                </div>

                <div class="border-t border-neutral-100 my-1"></div>

                {{-- Log Out --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900 transition-colors">
                        <x-heroicon-o-arrow-right-on-rectangle class="text-neutral-400 w-4 h-4" />
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
