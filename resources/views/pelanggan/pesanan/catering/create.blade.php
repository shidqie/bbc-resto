<x-layouts.landing>
    <x-slot:title>Pesan Katering — Saung Babakan Cinta</x-slot:title>
    <x-slot:description>Form pemesanan katering Saung Babakan Cinta — minimal 50 porsi, pemesanan H-3 sebelum acara.</x-slot:description>

    @include('pelanggan.pesanan.partials._map-styles')

    @php
        $orderConfig = [
            'type' => 'catering',
            'formId' => 'cateringForm',
            'minPorsi' => 50,
            'dpPersen' => 50,
            'batasHari' => 3,
            'satuanLabel' => 'Porsi',
            'qtyField' => 'jumlah_porsi',
            'previewUrl' => route('pesan.catering.preview'),
            'komponenUrl' => route('pesan.catering.komponen', ':id'),
            'hasGratisOngkir' => true,
            'minWarning' => 'Minimal order 50 porsi.',
        ];

        $summaryConfig = [
            'jenisLabel'  => 'Katering',
            'satuanLabel' => 'Porsi',
            'dpPersen'    => 50,
            'batasTeks'   => 'Pelunasan wajib dilakukan paling lambat H-3 sebelum tanggal pengambilan atau pengiriman pesanan.',
            'syarat'      => [
                'Konsumen wajib membayar uang muka (DP) sebesar 50% dari total nilai pesanan katering.',
                'Pembayaran dapat dilakukan melalui transfer ke rekening yang telah ditentukan oleh pihak Rumah Makan Saung Babakan Cinta.',
                'Apabila hingga batas waktu H-3 konsumen belum melakukan pelunasan, maka pesanan dianggap batal.',
                'DP yang telah dibayarkan dan diterima tidak dapat dikembalikan apabila pesanan dibatalkan oleh konsumen atau dibatalkan karena tidak dilakukan pelunasan sampai batas waktu yang ditentukan.',
            ],
            'hasGratisOngkir' => true,
        ];
    @endphp

    <section class="py-10 lg:py-14 bg-canvas min-h-screen bg-batik">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER --}}
            <div class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-5 sm:p-7 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <nav class="flex items-center gap-1.5 text-xs font-semibold text-body/50 mb-2">
                            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">Beranda</a>
                            <span>/</span><span>Layanan</span><span>/</span>
                            <span class="text-primary">Katering</span>
                        </nav>
                        <h1 class="text-2xl sm:text-3xl font-bold text-primary">Form Pemesanan Katering</h1>
                        <p class="text-sm text-body/70 mt-1.5">Minimal 50 porsi &middot; Pemesanan minimal H-3 sebelum acara &middot; DP 50%</p>
                    </div>
<a href="{{ url('/') }}"
                   onclick="event.preventDefault(); var href=this.href; Swal.fire({ title: 'Batalkan Pemesanan', text: 'Data yang sudah diisi akan hilang.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Batalkan', cancelButtonText: 'Batal', confirmButtonColor: '#DC2626', reverseButtons: true }).then(function (result) { if (result.isConfirmed) { window.location.href = href; } });"
                   class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl border border-primary/10 text-body text-base font-bold hover:bg-primary/5 hover:text-primary transition-colors self-start shrink-0">
                        <i class="ph ph-x-circle text-lg"></i>
                        Batal Pesan
                    </a>
                </div>
            </div>

            {{-- STEPPER PROGRESS --}}
            <div class="bg-surface border border-primary/10 rounded-2xl shadow-sm p-4 sm:p-6 mb-6">
                <div class="relative">
                    <div class="absolute left-[10%] right-[10%] top-4 h-0.5 bg-primary/10"></div>
                    <div class="relative grid grid-cols-5 gap-1">
                        @foreach([1 => 'Data Pemesan', 2 => 'Detail Acara', 3 => 'Pilih Paket', 4 => 'Pilih Menu', 5 => 'Pembayaran'] as $n => $stepLabel)
                            <div class="step-item flex flex-col items-center gap-1.5" data-step="{{ $n }}">
                                <div class="step-dot w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-primary/20 bg-surface flex items-center justify-center text-xs sm:text-sm font-bold text-body/40 transition-all duration-300">
                                    <span class="step-num">{{ $n }}</span>
                                    <i class="ph-bold ph-check step-check hidden"></i>
                                </div>
                                <span class="step-label hidden sm:block text-xs font-semibold text-body/50 leading-tight text-center">{{ $stepLabel }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <p id="stepper-hint" class="text-center text-xs text-body/60 mt-3 font-medium"></p>
            </div>

            @if($errors->any())
                <div class="bg-danger/5 border border-danger/20 text-danger rounded-2xl p-4 mb-6">
                    <div class="flex items-center gap-1.5 font-bold text-xs mb-1"><i class="ph-bold ph-warning-circle"></i> Periksa kembali isian Anda</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form id="cateringForm" method="POST" action="{{ route('pesan.catering.store') }}">
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
                                           min="{{ \Carbon\Carbon::today()->addDays(3)->format('Y-m-d') }}"
                                           value="{{ old('tanggal_acara') }}"
                                           class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface" required>
                                    <p id="tanggal-warning" class="text-danger text-xs mt-1 hidden">Pemesanan katering minimal H-3 sebelum acara.</p>
                                </div>
                                <div>
                                    <label for="jamAcara" class="block text-xs font-bold text-body mb-1">Jam Acara <span class="text-danger">*</span></label>
                                    <input type="time" id="jamAcara" name="jam_acara"
                                           value="{{ old('jam_acara') }}"
                                           class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface" required>
                                </div>

                                <div class="md:col-span-2">
                                    <x-ui.input-qty id="jumlahPorsi" name="jumlah_porsi" label="Jumlah Porsi" :value="old('jumlah_porsi', 50)" :required="true" min="50" stepper />
                                    <p class="text-xs text-body/50 font-medium mt-1.5 flex items-center gap-1"><i class="ph ph-info"></i> Minimal pemesanan 50 porsi.</p>
                                    <p id="jumlah-warning" class="text-danger text-xs mt-1 hidden">Minimal order 50 porsi.</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-body mb-2">Metode Pengiriman <span class="text-danger">*</span></label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label class="metode-card flex items-center gap-2.5 border border-primary bg-primary/5 rounded-xl px-4 py-3 cursor-pointer transition-all duration-200">
                                            <input type="radio" name="metode_pengiriman" value="pickup" class="sr-only metode-radio" checked>
                                            <span class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0"><i class="ph-bold ph-storefront"></i></span>
                                            <span>
                                                <span class="block text-sm font-bold text-body">Diambil (Pickup)</span>
                                                <span class="block text-xs text-body/60 font-medium">Ambil sendiri di rumah makan</span>
                                            </span>
                                        </label>
                                        <label class="metode-card flex items-center gap-2.5 border border-primary/10 bg-surface rounded-xl px-4 py-3 cursor-pointer transition-all duration-200">
                                            <input type="radio" name="metode_pengiriman" value="delivery" class="sr-only metode-radio">
                                            <span class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0"><i class="ph-bold ph-truck"></i></span>
                                            <span>
                                                <span class="block text-sm font-bold text-body">Diantar (Delivery)</span>
                                                <span class="block text-xs text-body/60 font-medium">Kirim ke lokasi acara</span>
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
                                        <h2 class="text-sm font-bold text-body">Pilih Paket Katering</h2>
                                        <p class="text-xs text-body/60 mt-0.5">Pilih salah satu paket yang sesuai kebutuhan acara Anda.</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold uppercase tracking-wide px-2.5 py-1 rounded-full bg-primary/10 text-primary shrink-0">Pilih 1</span>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($pakets as $paket)
                                    <label class="paket-card cursor-pointer border rounded-2xl p-4 transition-all duration-200 hover:border-primary hover:shadow-sm border-primary/10 bg-surface relative flex flex-col"
                                           data-paket-id="{{ $paket->id }}" data-harga="{{ $paket->harga_jual }}">
                                        <input type="radio" name="paket_id" value="{{ $paket->id }}" class="sr-only paket-radio" {{ old('paket_id') == $paket->id ? 'checked' : '' }} required>

                                        <div class="absolute top-3 right-3 w-6 h-6 rounded-full border-2 border-primary/20 bg-surface flex items-center justify-center opacity-0 selected-indicator transition-opacity">
                                            <i class="ph-bold ph-check text-primary text-xs"></i>
                                        </div>

                                        <div>
                                            @if($paket->foto)
                                                <img src="{{ Storage::url($paket->foto) }}" alt="{{ $paket->nama_menu }}" class="w-full h-44 object-cover rounded-xl mb-3">
                                            @else
                                                <div class="w-full h-44 rounded-xl bg-primary/[0.03] flex items-center justify-center mb-3 text-primary/30">
                                                    <i class="ph ph-package text-3xl"></i>
                                                </div>
                                            @endif
                                            <div class="mb-2">
                                                <h3 class="text-sm font-bold text-body leading-tight mb-1">{{ $paket->nama_menu }}</h3>
                                                <span class="text-xs font-bold text-primary">Rp {{ number_format($paket->harga_jual, 0, ',', '.') }} <span class="text-xs font-normal text-body/50">/porsi</span></span>
                                            </div>
                                            <p class="text-body/60 text-xs line-clamp-2 mb-3">{{ $paket->deskripsi }}</p>
                                            <ul class="text-xs text-body/70 divide-y divide-primary/5">
                                                @foreach($paket->komponen_paket->sortBy('urutan') as $komp)
                                                    <li class="flex items-center gap-1.5 py-1">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $komp->tipe_komponen === 'tetap' ? 'bg-primary' : 'bg-warning' }} flex-shrink-0"></span>
                                                        <span>{{ $komp->nama_komponen }}
                                                            @if($komp->tipe_komponen === 'pilihan')<span class="text-warning text-xs font-medium">(pilih 1)</span>@endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
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
                                    <h2 class="text-sm font-bold text-body">Pembayaran</h2>
                                    <p class="text-xs text-body/60 mt-0.5">Pilih skema pembayaran. Mendukung Transfer Bank BCA & QRIS (GoPay, OVO, Dana, m-Banking).</p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <label class="flex-1 flex items-center gap-3 border border-primary bg-primary/5 rounded-xl px-4 py-3 cursor-pointer transition-all duration-200">
                                    <input type="radio" name="opsi_pembayaran" value="dp" checked class="w-4 h-4 text-primary focus:ring-primary/20" onchange="updatePaymentLabel(this.value)">
                                    <div>
                                        <p class="text-sm font-bold text-body">Bayar DP (50%)</p>
                                        <p class="text-xs text-body/60 font-medium">Sisa dibayar H-3 sebelum acara</p>
                                    </div>
                                </label>
                                <label class="flex-1 flex items-center gap-3 border border-primary/10 bg-surface rounded-xl px-4 py-3 cursor-pointer hover:border-primary/40 transition-all duration-200">
                                    <input type="radio" name="opsi_pembayaran" value="lunas" class="w-4 h-4 text-primary focus:ring-primary/20" onchange="updatePaymentLabel(this.value)">
                                    <div>
                                        <p class="text-sm font-bold text-body">Bayar Lunas (100%)</p>
                                        <p class="text-xs text-body/60 font-medium">Selesaikan pembayaran sekaligus</p>
                                    </div>
                                </label>
                            </div>
                        </section>

                        {{-- SUBMIT (mobile: tombol submit ikut di bawah form, bukan hanya di summary) --}}
                        <button type="submit" class="lg:hidden w-full inline-flex items-center justify-center gap-2 bg-primary text-white font-bold text-base py-3.5 rounded-xl hover:bg-primary/90 transition-colors">
                            Bayar
                            <i class="ph-bold ph-arrow-right"></i>
                        </button>
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