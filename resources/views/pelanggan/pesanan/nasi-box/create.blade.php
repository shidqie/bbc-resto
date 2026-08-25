<x-layouts.landing>
    <x-slot:title>Pesan Nasi Box — Saung Babakan Cinta</x-slot:title>
    <x-slot:description>Form pemesanan nasi box Saung Babakan Cinta — minimal 20 box, pemesanan H-4 sebelum acara.</x-slot:description>

    @include('pelanggan.pesanan.partials._map-styles')

    @php
        $orderConfig = [
            'type' => 'nasibox',
            'formId' => 'nasiBoxForm',
            'minPorsi' => 20,
            'dpPersen' => 25,
            'batasHari' => 3,
            'satuanLabel' => 'Box',
            'qtyField' => 'jumlah_box',
            'previewUrl' => route('pesan.nasibox.preview', [], false),
            'komponenUrl' => route('pesan.nasibox.komponen', ':id', false),
            'komponenMap' => $komponenMap ?? [],
            'hasGratisOngkir' => false,
            'minWarning' => 'Minimal order 20 box.',
            'old' => [
                'nama_pemesan' => old('nama_pemesan', optional(auth('pelanggan')->user())->nama ?? ''),
                'kontak' => old('kontak', optional(auth('pelanggan')->user())->nomor_telepon ?? ''),
                'tanggal_acara' => old('tanggal_acara', ''),
                'jam_acara' => old('jam_acara', ''),
                'jumlah_box' => old('jumlah_box', 20),
                'metode_pengiriman' => old('metode_pengiriman', 'pickup'),
                'jam_pengambilan' => old('jam_pengambilan', ''),
                'lokasi_acara' => old('lokasi_acara', ''),
                'alamat_venue' => old('alamat_venue', ''),
                'latitude' => old('latitude', ''),
                'longitude' => old('longitude', ''),
                'jarak_km' => old('jarak_km', ''),
                'paket_id' => old('paket_id', $selectedPaketId ?? ''),
                'komponen' => old('komponen', []),
                'catatan' => old('catatan', ''),
                'opsi_pembayaran' => old('opsi_pembayaran', 'dp'),
            ]
        ];

        $summaryConfig = [
            'jenisLabel'  => 'Nasi Box',
            'satuanLabel' => 'Box',
            'dpPersen'    => 25,
            'batasTeks'   => 'Pelunasan dilakukan paling lambat H-3 sebelum hari acara.',
            'syarat'      => [
                'Pemesanan dilakukan minimal H-4 sebelum hari pelaksanaan acara.',
                'Dikenakan pembayaran uang muka (DP) sebesar 25%.',
                'Jika membatalkan pesanan, dikenakan potongan biaya 25% dari DP atau total yang telah dibayarkan.',
            ],
            'hasGratisOngkir' => false,
        ];
    @endphp

    <section class="py-10 lg:py-14 bg-canvas min-h-screen bg-batik">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER BANNER --}}
            <div class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-5 sm:p-7 mb-6 relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary"></div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative">
                    <div>
                        <nav class="flex items-center gap-1.5 text-xs font-semibold text-body/50 mb-2">
                            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">Beranda</a>
                            <span>/</span><span>Layanan</span><span>/</span>
                            <span class="text-primary">Nasi Box</span>
                        </nav>
                        <h1 class="text-2xl sm:text-3xl font-bold text-primary">Form Pemesanan Nasi Box</h1>
                        <p class="text-sm text-body/70 mt-1.5">Minimal 20 box &middot; Pemesanan minimal H-4 sebelum acara &middot; DP 25%</p>
                    </div>
                    <div class="flex items-center gap-2 self-start">
                        <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl border border-primary/10 text-body text-base font-bold hover:bg-primary/5 hover:text-primary transition-colors">
                            <x-heroicon-o-x-circle class="w-5 h-5 text-current" />
                            Batal Pesan
                        </a>
                    </div>
                </div>
            </div>

            {{-- STEPPER PROGRESS --}}
            <div class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-4 sm:p-5 mb-6">
                <div class="relative">
                    <div class="absolute left-10 right-10 top-[15px] h-0.5 bg-primary/10 hidden sm:block"></div>
                    <div class="relative grid grid-cols-5 gap-1">
                        @foreach([1 => 'Data Pemesan', 2 => 'Detail Acara', 3 => 'Pilih Paket', 4 => 'Pilih Menu', 5 => 'Pembayaran'] as $n => $stepLabel)
                            <div class="step-item flex flex-col items-center gap-1.5" data-step="{{ $n }}">
                                <div class="step-dot w-8 h-8 rounded-full border-2 border-primary/20 bg-surface flex items-center justify-center text-sm font-bold text-body/40 transition-all duration-300">
                                    <span class="step-num">{{ $n }}</span>
                                    <x-heroicon-s-check class="w-4 h-4 step-check hidden text-white" />
                                </div>
                                <span class="step-label text-[10px] sm:text-xs font-semibold text-body/50 leading-tight text-center">{{ $stepLabel }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <p id="stepper-hint" class="text-center text-xs text-body/60 mt-3 font-medium"></p>
            </div>

            @if($errors->any())
                <div class="bg-danger/5 border border-danger/20 text-danger rounded-2xl p-4 mb-6">
                    <div class="flex items-center gap-1.5 font-bold text-xs mb-1"><x-heroicon-o-exclamation-triangle class="w-4 h-4" /> Periksa kembali isian Anda</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form id="nasiBoxForm" method="POST" action="{{ route('pesan.nasibox.store') }}">
                @csrf
                <div class="grid lg:grid-cols-3 gap-6 items-start">

                    {{-- LEFT COLUMN: FORM --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- SECTION 1: Data Pemesan --}}
                        <section class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-5 sm:p-6">
                            <div class="flex items-start gap-3 mb-5">
                                <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shrink-0">1</span>
                                <div>
                                    <h2 class="text-sm font-bold text-body">Data Pemesan</h2>
                                    <p class="text-xs text-body/60 mt-0.5">Siapa yang memesan dan nomor yang bisa dihubungi.</p>
                                </div>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nama_pemesan" class="block text-xs font-bold text-body mb-1">Nama Pemesan / Instansi <span class="text-danger">*</span></label>
                                    <input type="text" id="nama_pemesan" name="nama_pemesan" value="{{ old('nama_pemesan', optional(auth('pelanggan')->user())->nama ?? '') }}"
                                           placeholder="Contoh: PT Sejahtera / Andi"
                                           class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body placeholder-body/30 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface" required>
                                </div>
                                <div>
                                    <x-input-wa name="kontak" label="Nomor Telepon / WhatsApp" :value="optional(auth('pelanggan')->user())->nomor_telepon ?? ''" :required="true" />
                                </div>
                            </div>
                        </section>

                        {{-- SECTION 2: Detail Acara --}}
                        <section class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-5 sm:p-6">
                            <div class="flex items-start gap-3 mb-5">
                                <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shrink-0">2</span>
                                <div>
                                    <h2 class="text-sm font-bold text-body">Detail Acara</h2>
                                    <p class="text-xs text-body/60 mt-0.5">Waktu pelaksanaan, jumlah pesanan, dan cara pengambilan/pengiriman.</p>
                                </div>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4 items-start">
                                <div>
                                    <label for="tanggalAcara" class="block text-xs font-bold text-body mb-1">Tanggal Acara <span class="text-danger">*</span></label>
                                    <input type="date" id="tanggalAcara" name="tanggal_acara"
                                           min="{{ \Carbon\Carbon::today()->addDays(4)->format('Y-m-d') }}"
                                           value="{{ old('tanggal_acara') }}"
                                           class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface" required>
                                    <p id="tanggal-warning" class="text-danger text-xs mt-1 hidden">Pemesanan Nasi Box minimal H-4 sebelum acara.</p>
                                </div>
                                <div>
                                    <label for="jamAcara" class="block text-xs font-bold text-body mb-1">Jam Acara <span class="text-danger">*</span></label>
                                    <input type="time" id="jamAcara" name="jam_acara"
                                           value="{{ old('jam_acara') }}"
                                           class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface" required>
                                </div>

                                <div class="md:col-span-2">
                                    <x-ui.input-qty id="jumlahBox" name="jumlah_box" label="Jumlah Box" :value="old('jumlah_box', 20)" :required="true" min="20" stepper />
                                    <p class="text-xs text-body/50 font-medium mt-1.5 flex items-center gap-1"><x-heroicon-o-information-circle class="w-4 h-4 inline-block text-primary/70 shrink-0" /> Minimal pemesanan 20 box.</p>
                                    <p id="jumlah-warning" class="text-danger text-xs mt-1 hidden">Minimal order 20 box.</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-body mb-2">Metode Pengiriman <span class="text-danger">*</span></label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label class="metode-card flex items-center gap-2.5 border {{ old('metode_pengiriman', 'pickup') === 'pickup' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-primary/10 bg-surface' }} rounded-xl px-4 py-3 cursor-pointer transition-all duration-200">
                                            <input type="radio" name="metode_pengiriman" value="pickup" class="sr-only metode-radio" {{ old('metode_pengiriman', 'pickup') === 'pickup' ? 'checked' : '' }}>
                                            <span class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0"><x-heroicon-o-building-storefront class="w-5 h-5" /></span>
                                            <span>
                                                <span class="block text-sm font-bold text-body">Diambil di Resto</span>
                                                <span class="block text-xs text-body/60 font-medium">Ambil langsung di rumah makan</span>
                                            </span>
                                        </label>
                                        <label class="metode-card flex items-center gap-2.5 border {{ old('metode_pengiriman') === 'delivery' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-primary/10 bg-surface' }} rounded-xl px-4 py-3 cursor-pointer transition-all duration-200">
                                            <input type="radio" name="metode_pengiriman" value="delivery" class="sr-only metode-radio" {{ old('metode_pengiriman') === 'delivery' ? 'checked' : '' }}>
                                            <span class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0"><x-heroicon-o-truck class="w-5 h-5" /></span>
                                            <span>
                                                <span class="block text-sm font-bold text-body">Diantar ke Lokasi</span>
                                                <span class="block text-xs text-body/60 font-medium">Kirim langsung ke lokasi tujuan</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="jamPengambilan" class="block text-xs font-bold text-body mb-1">Jam Pengambilan / Pengiriman <span class="text-danger">*</span></label>
                                    <input type="time" id="jamPengambilan" name="jam_pengambilan"
                                           value="{{ old('jam_pengambilan') }}"
                                           class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface" required>
                                </div>

                                @include('pelanggan.pesanan.partials._delivery-map')
                            </div>
                        </section>

                        {{-- SECTION 3: Pilih Paket --}}
                        <section class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-3 mb-5">
                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shrink-0">3</span>
                                    <div>
                                        <h2 class="text-sm font-bold text-body">Pilih Paket Nasi Box</h2>
                                        <p class="text-xs text-body/60 mt-0.5">Pilih salah satu paket yang sesuai kebutuhan acara Anda.</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold uppercase tracking-wide px-2.5 py-1 rounded-full bg-primary/10 text-primary shrink-0">Pilih 1</span>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($pakets as $paket)
                                    <label class="paket-card cursor-pointer border rounded-2xl p-4 transition-all duration-200 hover:border-primary hover:shadow-sm border-primary/10 bg-surface relative flex flex-col"
                                           data-paket-id="{{ $paket->id }}" data-harga="{{ $paket->harga_jual }}">
                                        <input type="radio" name="paket_id" value="{{ $paket->id }}" class="sr-only paket-radio" {{ old('paket_id', $selectedPaketId) == $paket->id ? 'checked' : '' }} required>
                                        <div>
                                            @if($paket->foto)
                                                <img src="{{ $paket->foto_url ?? Storage::url($paket->foto) }}" alt="{{ $paket->nama_menu }}" class="w-full h-36 object-cover rounded-xl mb-3">
                                            @else
                                                <div class="w-full h-36 rounded-xl bg-primary/[0.03] flex items-center justify-center mb-3 text-primary/30">
                                                    <x-heroicon-o-cube class="w-10 h-10" />
                                                </div>
                                            @endif
                                            <div class="mb-2">
                                                <h3 class="text-sm font-bold text-body leading-tight mb-1">{{ $paket->nama_menu }}</h3>
                                                <span class="text-xs font-bold text-primary">Rp {{ number_format($paket->harga_jual, 0, ',', '.') }} <span class="text-xs font-normal text-body/50">/box</span></span>
                                            </div>
                                            <p class="text-body/60 text-xs line-clamp-2 mb-3">{{ $paket->deskripsi }}</p>
                                            <ul class="text-xs text-body/70 space-y-1 mb-3">
                                                @foreach($paket->komponen_paket->sortBy('urutan') as $komp)
                                                    <li class="flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $komp->tipe_komponen === 'tetap' ? 'bg-primary' : 'bg-warning' }} flex-shrink-0"></span>
                                                        <span>{{ $komp->nama_komponen }}
                                                            @if($komp->tipe_komponen === 'pilihan')<span class="text-warning text-xs font-medium">(pilih 1)</span>@endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="mt-auto pt-2">
                                            <div class="text-xs font-bold bg-primary text-white px-2.5 py-1 rounded-full w-max opacity-0 selected-indicator transition-opacity">✓ Dipilih</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        {{-- SECTION 4: Pilih Menu --}}
                        <section id="sec-komponen" class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-5 sm:p-6 hidden">
                            <div class="flex items-start gap-3 mb-5">
                                <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shrink-0">4</span>
                                <div>
                                    <h2 class="text-sm font-bold text-body">Pilih Item Menu</h2>
                                    <p class="text-xs text-body/60 mt-0.5">Sesuaikan pilihan menu dari paket yang Anda pilih.</p>
                                </div>
                            </div>
                            <div id="komponen-container" class="space-y-4"></div>
                            <div class="mt-4 pt-4 border-t border-primary/10">
                                <label for="catatan" class="block text-xs font-bold text-body mb-1.5">Catatan Tambahan</label>
                                <textarea id="catatan" name="catatan" rows="2" placeholder="Contoh: Kurangi pedas, tambahkan sambal terpisah, dsb."
                                          class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body placeholder-body/30 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface">{{ old('catatan') }}</textarea>
                            </div>
                        </section>

                        {{-- SECTION 5: Pembayaran --}}
                        <section class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-5 sm:p-6">
                            <div class="flex items-start gap-3 mb-5">
                                <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shrink-0">5</span>
                                <div>
                                    <h2 class="text-sm font-bold text-body">Metode Pembayaran</h2>
                                    <p class="text-xs text-body/60 mt-0.5">Pilih skema pembayaran nasi box (DP 25% atau Pelunasan Langsung).</p>
                                </div>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-3">
                                <label class="metode-bayar-card flex items-center gap-3 border-2 {{ old('opsi_pembayaran', 'dp') === 'dp' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-primary/10 bg-surface' }} rounded-xl p-3.5 cursor-pointer transition-all duration-200">
                                    <input type="radio" name="opsi_pembayaran" value="dp" class="sr-only bayar-radio" {{ old('opsi_pembayaran', 'dp') === 'dp' ? 'checked' : '' }}>
                                    <span class="radio-dot w-4 h-4 rounded-full border-2 {{ old('opsi_pembayaran', 'dp') === 'dp' ? 'border-primary' : 'border-body/20' }} flex items-center justify-center shrink-0">
                                        <span class="w-2 h-2 rounded-full {{ old('opsi_pembayaran', 'dp') === 'dp' ? 'bg-primary' : 'bg-transparent' }}"></span>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-body">Bayar DP (25%)</p>
                                        <p class="text-xs text-body/60 font-medium">Sisa dibayar maksimal H-3 sebelum acara</p>
                                    </div>
                                </label>
                                <label class="metode-bayar-card flex items-center gap-3 border-2 {{ old('opsi_pembayaran') === 'lunas' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-primary/10 bg-surface' }} rounded-xl p-3.5 cursor-pointer transition-all duration-200">
                                    <input type="radio" name="opsi_pembayaran" value="lunas" class="sr-only bayar-radio" {{ old('opsi_pembayaran', 'lunas') === 'lunas' ? 'checked' : '' }}>
                                    <span class="radio-dot w-4 h-4 rounded-full border-2 {{ old('opsi_pembayaran', 'lunas') === 'lunas' ? 'border-primary' : 'border-body/20' }} flex items-center justify-center shrink-0">
                                        <span class="w-2 h-2 rounded-full {{ old('opsi_pembayaran', 'lunas') === 'lunas' ? 'bg-primary' : 'bg-transparent' }}"></span>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-body">Bayar Lunas (100%)</p>
                                        <p class="text-xs text-body/60 font-medium">Selesaikan pembayaran sekaligus</p>
                                    </div>
                                </label>
                            </div>
                        </section>
                    </div>

                    {{-- RIGHT COLUMN: RINGKASAN (STICKY) --}}
                    <div class="lg:col-span-1 sticky top-28">
                        @include('pelanggan.pesanan.partials._summary', ['config' => $summaryConfig])
                    </div>
                </div>
            </form>
        </div>
    </section>

    @push('scripts')
        @include('pelanggan.pesanan.partials._order-script', ['config' => $orderConfig])

    @endpush
</x-layouts.landing>