@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-[#F8FAFC] text-[#111827] font-sans p-6 md:p-8 space-y-6"
     x-data="{ activeTab: (window.location.hash === '#update-password' || (new URLSearchParams(window.location.search)).get('tab') === 'password') ? 'password' : 'profile' }">
    <div class="max-w-4xl mx-auto w-full space-y-6">

        {{-- Minimalist Header Greeting --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-gray-200/80">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                    <x-heroicon-o-cog class="text-[#0F2E23] w-7 h-7" /> Pengaturan Akun Saya
                </h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Kelola data profil pengguna & keamanan kata sandi akun Anda.</p>
            </div>
        </div>

        {{-- Navigation Tabs: Edit Profile & Ubah Password (Terpisah) --}}
        <div class="flex border-b border-slate-200 gap-2 text-sm font-extrabold">
            <button type="button" @click="activeTab = 'profile'; window.location.hash = 'edit-profile'"
                    :class="activeTab === 'profile' ? 'text-[#0F2E23] border-b-2 border-[#0F2E23] pb-3 px-4 font-black' : 'text-slate-400 hover:text-slate-600 pb-3 px-4'"
                    class="transition-all flex items-center gap-2">
                <x-heroicon-o-pencil class="w-5 h-5" />
                <span>Edit Informasi Profil</span>
            </button>
            <button type="button" @click="activeTab = 'password'; window.location.hash = 'update-password'"
                    :class="activeTab === 'password' ? 'text-[#0F2E23] border-b-2 border-[#0F2E23] pb-3 px-4 font-black' : 'text-slate-400 hover:text-slate-600 pb-3 px-4'"
                    class="transition-all flex items-center gap-2">
                <x-heroicon-o-key class="text-amber-600 w-5 h-5" />
                <span>Ubah Kata Sandi</span>
            </button>
        </div>

        {{-- TAB 1: Form Edit Informasi Profil --}}
        <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-150"
             class="bg-white rounded-3xl p-6 md:p-8 border border-gray-200/90 shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- TAB 2: Form Ubah Password --}}
        <div x-show="activeTab === 'password'" x-transition:enter="transition ease-out duration-150"
             class="bg-white rounded-3xl p-6 md:p-8 border border-gray-200/90 shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

    </div>
</div>
@endsection
