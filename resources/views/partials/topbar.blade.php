

{{--
|--------------------------------------------------------------------------
| Admin Topbar Header (Vision UI Reference Theme)
|--------------------------------------------------------------------------
| Menampilkan 3 elemen utama di sudut kanan topbar:
| 1. Waktu (Date & Time Pill)
| 2. Notif (Black Circular Bell Button with Red Badge)
| 3. Profile (User Profile Pill Avatar Dropdown)
--}}

<header class="no-print h-16 bg-white/90 backdrop-blur-md border-b border-gray-100 px-6 flex items-center justify-between shrink-0 z-[100] shadow-xs">
    {{-- Area Kiri: Halo Role Greeting (Hanya Muncul di Dashboard & Point of Sale) --}}
    <div class="flex items-center gap-3">
        @if(request()->routeIs('dashboard') || request()->routeIs('pos.*'))
            @php
                $roleName = auth()->user()->role->name ?? 'Admin';
            @endphp
            <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2 tracking-tight">
                <i class="fa-solid fa-user-shield text-[#0F2E23] text-sm"></i> Halo, {{ $roleName }}
            </h2>
        @endif
    </div>

    <div class="flex items-center gap-3">
        {{-- 3. Profile --}}
        <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 p-1 pl-3 bg-gray-100/80 hover:bg-gray-200/80 border border-gray-200/50 rounded-full transition-all focus:outline-none shadow-2xs">
                <span class="text-xs font-bold text-gray-800 hidden md:inline truncate max-w-[120px]">{{ auth()->user()->name ?? 'Admin' }}</span>
                <div class="w-8 h-8 rounded-full bg-[#111827] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
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
                 class="absolute right-0 mt-2 w-56 rounded-2xl bg-white border border-gray-100 shadow-xl z-50 p-2 overflow-hidden"
                 style="display: none;">
                
                {{-- Header Profile Info --}}
                <div class="px-3 py-2 border-b border-gray-100 mb-1">
                    <p class="text-xs font-bold text-gray-900 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-[10px] text-gray-500 truncate">{{ auth()->user()->role->name ?? 'Admin' }}</p>
                </div>

                {{-- My Profile Group --}}
                <div class="py-1">
                    <p class="px-3 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider mb-1">My Profile</p>
                    
                    {{-- Edit Profile Link --}}
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <i class="fa-solid fa-user-pen text-[#0F2E23] text-xs w-4"></i>
                        <span>Edit Profile</span>
                    </a>

                    {{-- Ubah Password Link --}}
                    <a href="{{ route('profile.edit') }}#update-password" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <i class="fa-solid fa-key text-amber-600 text-xs w-4"></i>
                        <span>Ubah Password</span>
                    </a>
                </div>

                <div class="border-t border-gray-100 my-1"></div>

                {{-- Log Out --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-red-600 hover:bg-red-50 transition-colors">
                        <i class="fa-solid fa-right-from-bracket text-xs w-4"></i>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
