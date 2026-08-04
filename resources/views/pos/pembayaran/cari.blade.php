<x-layouts.landing>
    <x-slot:title>Bayar Tagihan</x-slot:title>

    <div class="min-h-screen bg-[#FFFFFF] text-[#111827]">
        <header class="py-12 border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-6 md:px-12">
                <h1 class="text-[40px] font-medium leading-tight tracking-tight mb-2 text-[#0D3024]">Bayar Tagihan</h1>
                <p class="text-gray-500 text-base font-light">Masukkan nomor invoice pesanan Anda untuk melanjutkan pembayaran.</p>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 md:px-12 py-12 lg:py-16">
            @if(session('error'))
                <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('success') }}</div>
            @endif

            <div class="max-w-2xl mx-auto">
                <div class="border-2 border-[#0D3024]/15 rounded-xl p-8 bg-emerald-50/40">
                    <div class="w-12 h-12 rounded-xl bg-[#0D3024] text-[#D4A843] flex items-center justify-center mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h2m4 0h4M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                    </div>
                    <h2 class="text-xl font-medium text-gray-900 mb-2">Cari Pesanan Anda</h2>
                    <p class="text-sm text-gray-500 mb-6">Nomor invoice tertera pada bukti pesanan, contoh: <span class="font-mono text-[#0D3024] font-medium">CTR-20260802-001</span></p>

                    <form action="{{ route('pesanan.bayar.cari') }}" method="GET" class="space-y-4">
                        <div>
                            <label for="kode_pesanan" class="block text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Nomor Invoice</label>
                            <input type="text" id="kode_pesanan" name="kode_pesanan" value="{{ old('kode_pesanan') }}" required placeholder="Contoh: CTR-20260802-001"
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-white text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#0D3024]/30 focus:border-[#0D3024] placeholder:text-gray-300">
                            @error('kode_pesanan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-[#0D3024] hover:bg-[#164032] text-white font-bold tracking-widest text-sm uppercase rounded-xl transition-all border border-[#0D3024] active:scale-[0.99]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            Lanjutkan ke Pembayaran
                        </button>
                    </form>
                </div>

                <div class="mt-6 space-y-2 text-center">
                    <a href="{{ route('lacak.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold tracking-widest text-xs uppercase rounded-xl transition-all border border-emerald-200/50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Lacak / Cek Status Pesanan
                    </a>
                </div>
            </div>
        </main>
    </div>
</x-layouts.landing>
