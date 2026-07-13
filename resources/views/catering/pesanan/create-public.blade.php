<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesan Catering - Saung Babakan Cinta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-orange-50 to-amber-50 min-h-screen font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-primary">Saung Babakan Cinta</h1>
                <p class="text-xs text-gray-500">Catering & Nasi Box</p>
            </div>
            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-primary">Login Admin</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Pesan Catering / Nasi Box</h2>
            <p class="text-gray-500 mt-2">Isi formulir di bawah ini untuk memesan paket catering atau nasi box untuk acara Anda.</p>
        </div>

        @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <ul class="text-red-700 text-sm list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('catering.pesan.store') }}" method="POST" class="space-y-6" x-data="orderForm()">
            @csrf

            <!-- Pilih Paket -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold mb-4"><i class="fa-solid fa-box-open mr-2 text-primary"></i>Pilih Paket</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($pakets as $paket)
                    <label class="cursor-pointer">
                        <input type="radio" name="paket_catering_id" value="{{ $paket->id }}" x-model="selectedPaket" @change="updatePaket({{ $paket->id }}, {{ $paket->harga }}, '{{ $paket->jenis_paket }}')" class="peer hidden" {{ old('paket_catering_id') == $paket->id ? 'checked' : '' }}>
                        <div class="border-2 border-gray-200 rounded-xl p-4 peer-checked:border-primary peer-checked:bg-orange-50 transition-all hover:border-gray-300">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $paket->jenis_paket === 'catering' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $paket->jenis_paket === 'catering' ? 'Catering' : 'Nasi Box' }}</span>
                                    <h4 class="font-semibold text-gray-900 mt-2">{{ $paket->nama_paket }}</h4>
                                    @if($paket->deskripsi)
                                    <p class="text-xs text-gray-500 mt-1">{{ $paket->deskripsi }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-primary">Rp {{ number_format($paket->harga, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">/porsi</p>
                                </div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Data Pemesan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold mb-4"><i class="fa-solid fa-user mr-2 text-primary"></i>Data Pemesan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama_pemesan" value="{{ old('nama_pemesan') }}" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon / WhatsApp *</label>
                        <input type="text" name="no_telepon" value="{{ old('no_telepon') }}" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Porsi *</label>
                        <input type="number" name="jumlah_porsi" x-model="jumlahPorsi" value="{{ old('jumlah_porsi', 1) }}" required min="1" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Pengiriman *</label>
                    <textarea name="alamat_pengiriman" required rows="2" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">{{ old('alamat_pengiriman') }}</textarea>
                </div>
            </div>

            <!-- Detail Acara -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold mb-4"><i class="fa-solid fa-calendar mr-2 text-primary"></i>Detail Acara</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Acara *</label>
                        <input type="date" name="tanggal_acara" value="{{ old('tanggal_acara') }}" required class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary">
                        <p class="text-xs text-gray-400 mt-1" x-show="jenisPaket === 'catering'">Minimal 14 hari dari sekarang</p>
                        <p class="text-xs text-gray-400 mt-1" x-show="jenisPaket === 'nasi_box'">Minimal 2 hari dari sekarang</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Acara</label>
                        <textarea name="detail_acara" rows="2" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary" placeholder="Contoh: Pernikahan, Ulang tahun, dll.">{{ old('detail_acara') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="bg-white rounded-xl shadow-sm border border-primary/30 p-6" x-show="selectedPaket">
                <h3 class="text-lg font-semibold mb-3"><i class="fa-solid fa-receipt mr-2 text-primary"></i>Ringkasan</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Harga / porsi:</span><span class="font-medium">Rp <span x-text="hargaFormatted"></span></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Jumlah porsi:</span><span class="font-medium" x-text="jumlahPorsi"></span></div>
                    <hr>
                    <div class="flex justify-between text-lg"><span class="font-semibold">Total:</span><span class="font-bold text-primary">Rp <span x-text="totalFormatted"></span></span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">DP (<span x-text="dpPersen"></span>%):</span><span class="font-semibold text-red-600">Rp <span x-text="dpFormatted"></span></span></div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-white rounded-xl font-semibold text-lg hover:bg-orange-700 transition-colors shadow-lg shadow-orange-200">
                <i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan
            </button>
        </form>
    </main>

    <script>
    function orderForm() {
        return {
            selectedPaket: '{{ old("paket_catering_id", "") }}',
            harga: 0,
            jumlahPorsi: {{ old('jumlah_porsi', 1) }},
            jenisPaket: '',
            updatePaket(id, harga, jenis) {
                this.harga = harga;
                this.jenisPaket = jenis;
            },
            get total() { return this.harga * this.jumlahPorsi; },
            get dpPersen() { return this.jenisPaket === 'catering' ? 50 : 25; },
            get dp() { return this.total * (this.dpPersen / 100); },
            get hargaFormatted() { return this.harga.toLocaleString('id-ID'); },
            get totalFormatted() { return this.total.toLocaleString('id-ID'); },
            get dpFormatted() { return this.dp.toLocaleString('id-ID'); }
        }
    }
    </script>
</body>
</html>
