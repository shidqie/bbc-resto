@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-[#F8FAFC] text-[#111827] font-sans p-6 md:p-8 space-y-6"
     x-data="{ activeTab: (window.location.hash === '#update-password' || (new URLSearchParams(window.location.search)).get('tab') === 'password') ? 'password' : 'profile' }">
    <div class="max-w-4xl mx-auto w-full space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-neutral-200">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-900 tracking-tight flex items-center gap-2">
                    <x-heroicon-o-cog class="text-neutral-400 w-7 h-7" /> Pengaturan Akun Saya
                </h1>
                <p class="text-xs text-neutral-500 font-medium mt-0.5">Kelola data profil pengguna & keamanan kata sandi akun Anda.</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-neutral-200 gap-2 text-sm font-semibold">
            <button type="button" @click="activeTab = 'profile'; window.location.hash = 'edit-profile'"
                    :class="activeTab === 'profile' ? 'text-neutral-900 border-b-2 border-neutral-900 pb-3 px-4' : 'text-neutral-400 hover:text-neutral-600 pb-3 px-4'"
                    class="transition-all flex items-center gap-2">
                <x-heroicon-o-pencil class="w-4 h-4" />
                <span>Edit Informasi Profil</span>
            </button>
            <button type="button" @click="activeTab = 'password'; window.location.hash = 'update-password'"
                    :class="activeTab === 'password' ? 'text-neutral-900 border-b-2 border-neutral-900 pb-3 px-4' : 'text-neutral-400 hover:text-neutral-600 pb-3 px-4'"
                    class="transition-all flex items-center gap-2">
                <x-heroicon-o-key class="w-4 h-4" />
                <span>Ubah Kata Sandi</span>
            </button>
        </div>

        {{-- TAB 1: Edit Profil --}}
        <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-150"
             class="bg-white rounded-2xl p-6 md:p-8 border border-neutral-200">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- TAB 2: Ubah Password --}}
        <div x-show="activeTab === 'password'" x-transition:enter="transition ease-out duration-150"
             class="bg-white rounded-2xl p-6 md:p-8 border border-neutral-200">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

    </div>
</div>
@endsection
