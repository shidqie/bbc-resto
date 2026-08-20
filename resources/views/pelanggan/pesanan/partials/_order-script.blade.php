<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
    const cfg = @json($config);

    const minDate = "{{ \Carbon\Carbon::today()->addDays(4)->format('Y-m-d') }}";
    const bbcLat = -6.8244057;
    const bbcLng = 107.5289353;

    let hargaSatuan = 0;
    let selectedPaketId = null;
    let metodePengiriman = 'pickup';
    let jarakKm = 0;
    let currentTotal = 0;
    let currentDp = 0;

    let map, marker, restoMarker;

    function notifyError(msg) {
        if (window.showToast) {
            window.showToast('error', msg);
        } else if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Maaf', text: msg });
        }
    }

    /* ============================================================
       PETA & JARAK
       ============================================================ */
    function initMap() {
        if (map) return;

        map = L.map('map', { zoomControl: false }).setView([bbcLat, bbcLng], 14);
        L.control.zoom({ position: 'bottomleft' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const restoIcon = L.divIcon({
            html: '<div class="w-8 h-8 bg-white border border-primary/10 shadow-sm rounded-full flex items-center justify-center text-primary"><i class="ph-bold ph-storefront text-lg"></i></div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            tooltipAnchor: [0, -20]
        });

        restoMarker = L.marker([bbcLat, bbcLng], { icon: restoIcon }).addTo(map);
        restoMarker.bindTooltip("Saung Babakan Cinta", { permanent: true, direction: 'top', offset: [0, -10], className: 'resto-tooltip' }).openTooltip();

        let initLat = document.getElementById('inputLat').value || bbcLat;
        let initLng = document.getElementById('inputLng').value || bbcLng;

        if (document.getElementById('inputLat').value) {
            map.setView([initLat, initLng], 15);
            hitungJarakOSRM(bbcLat, bbcLng, initLat, initLng);
        } else {
            locateUser(false);
        }

        // Handle Map Drag for Fixed Center Pin
        map.on('move', function () {
            const centerMarker = document.getElementById('center-marker');
            if (centerMarker) centerMarker.style.transform = 'translate(-50%, -60%)';
        });

        let moveTimeout;
        map.on('moveend', function () {
            const centerMarker = document.getElementById('center-marker');
            if (centerMarker) centerMarker.style.transform = 'translate(-50%, -50%)';

            clearTimeout(moveTimeout);
            moveTimeout = setTimeout(() => {
                const center = map.getCenter();
                document.getElementById('inputLat').value = center.lat;
                document.getElementById('inputLng').value = center.lng;
                hitungJarakOSRM(bbcLat, bbcLng, center.lat, center.lng);
                updateAlamatText(center.lat, center.lng);
            }, 500);
        });

        setupMapSearch();
    }

    function locateUser(showAlert = true) {
        if (showAlert) document.getElementById('cardAlamat').textContent = "Mencari lokasi GPS...";

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                map.setView([userLat, userLng], 15);
            }, function () {
                console.log("Geolocation error");
                document.getElementById('cardAlamat').textContent = "Geser peta untuk menentukan lokasi...";
                if (showAlert) notifyError("Tidak bisa mengakses lokasi. Silakan cek izin browser Anda.");
            }, { enableHighAccuracy: true });
        } else {
            document.getElementById('cardAlamat').textContent = "Geser peta untuk menentukan lokasi...";
            if (showAlert) notifyError("Browser Anda tidak mendukung fitur lokasi GPS.");
        }
    }

    function setupMapSearch() {
        const input = document.getElementById('map-search-input');
        const btn = document.getElementById('map-search-btn');
        const resultsBox = document.getElementById('map-search-results');

        if (!input || !btn || !resultsBox) return;

        const doSearch = async () => {
            const query = input.value.trim();
            if (query.length < 3) {
                resultsBox.classList.add('hidden');
                return;
            }

            try {
                btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i>';
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=id`);
                const data = await res.json();
                
                resultsBox.innerHTML = '';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'p-3 hover:bg-primary/5 cursor-pointer text-sm text-body transition-colors';
                        div.innerHTML = `<div class="font-bold text-primary mb-0.5 truncate">${item.display_name.split(',')[0]}</div>
                                         <div class="text-xs text-body/60 truncate">${item.display_name}</div>`;
                        div.onclick = () => {
                            map.setView([item.lat, item.lon], 16);
                            input.value = item.display_name.split(',')[0];
                            resultsBox.classList.add('hidden');
                        };
                        resultsBox.appendChild(div);
                    });
                    resultsBox.classList.remove('hidden');
                } else {
                    resultsBox.innerHTML = '<div class="p-3 text-sm text-body/60 text-center">Tidak ditemukan.</div>';
                    resultsBox.classList.remove('hidden');
                }
            } catch (e) {
                console.error("Search Error", e);
            } finally {
                btn.textContent = 'Cari';
            }
        };

        btn.addEventListener('click', doSearch);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                doSearch();
            }
        });

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !btn.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.classList.add('hidden');
            }
        });
    }

    async function updateAlamatText(lat, lng) {
        document.getElementById('cardAlamat').textContent = "Mencari alamat...";
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await res.json();
            if (data && data.display_name) {
                const shortAddr = data.display_name.split(',').slice(0, 3).join(',');
                document.getElementById('cardAlamat').textContent = shortAddr;
                if (!document.getElementById('alamatDelivery').value) {
                    document.getElementById('alamatDelivery').value = data.display_name;
                }
            } else {
                document.getElementById('cardAlamat').textContent = "Lokasi ditandai";
            }
        } catch (e) {
            document.getElementById('cardAlamat').textContent = "Lokasi ditandai";
        }
    }

    async function hitungJarakOSRM(lat1, lng1, lat2, lng2) {
        try {
            const res = await fetch(`https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=false`);
            const data = await res.json();
            if (data.routes && data.routes.length > 0) {
                jarakKm = data.routes[0].distance / 1000;
                document.getElementById('inputJarak').value = jarakKm.toFixed(2);
                document.getElementById('textJarak').textContent = jarakKm.toFixed(2) + ' km';

                document.getElementById('jarakWarning').classList.add('hidden');
                hitungTotalPreview();
            }
        } catch (e) {
            console.error("OSRM Error", e);
        }
    }

    /* ============================================================
       METODE PENGIRIMAN
       ============================================================ */
    document.querySelectorAll('.metode-radio').forEach(r => {
        r.addEventListener('change', (e) => {
            metodePengiriman = e.target.value;

            document.querySelectorAll('.metode-card').forEach(c => {
                const selected = c.querySelector('.metode-radio').checked;
                c.classList.toggle('border-primary', selected);
                c.classList.toggle('bg-primary/5', selected);
                c.classList.toggle('ring-1', selected);
                c.classList.toggle('ring-primary', selected);
                c.classList.toggle('border-primary/10', !selected);
                c.classList.toggle('bg-surface', !selected);
            });

            if (metodePengiriman === 'delivery') {
                document.getElementById('deliverySection').classList.remove('hidden');
                document.getElementById('alamatDelivery').required = true;
                document.getElementById('alamatDelivery').name = 'lokasi_acara';
                setTimeout(initMap, 200);
            } else {
                document.getElementById('deliverySection').classList.add('hidden');
                document.getElementById('alamatDelivery').required = false;
                document.getElementById('alamatDelivery').name = '';
            }
            hitungTotalPreview();
        });
    });

    /* ============================================================
       PILIH PAKET
       ============================================================ */
    document.querySelectorAll('.paket-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.paket-card').forEach(c => {
                c.classList.remove('border-primary', 'bg-primary/5', 'ring-1', 'ring-primary');
                c.classList.add('border-primary/10', 'bg-surface');
                c.querySelector('.selected-indicator').style.opacity = '0';
            });
            card.classList.add('border-primary', 'bg-primary/5', 'ring-1', 'ring-primary');
            card.classList.remove('border-primary/10', 'bg-surface');
            card.querySelector('.selected-indicator').style.opacity = '1';
            card.querySelector('.paket-radio').checked = true;

            selectedPaketId = card.dataset.paketId;
            hargaSatuan = parseInt(card.dataset.harga);

            const pName = card.querySelector('div h3') ? card.querySelector('div h3').textContent : 'Paket Terpilih';
            document.getElementById('summary-paket').textContent = pName;

            loadKomponen(selectedPaketId);
            hitungTotalPreview();
        });
    });

    async function loadKomponen(paketId) {
        const sec = document.getElementById('sec-komponen');
        const container = document.getElementById('komponen-container');
        sec.classList.remove('hidden');
        container.innerHTML = '<p class="text-body/50 text-xs font-medium">Memuat komponen menu...</p>';

        try {
            const res = await fetch(cfg.komponenUrl.replace(':id', paketId));
            const komponens = await res.json();

            container.innerHTML = '';
            komponens.forEach(komp => {
                const div = document.createElement('div');
                div.className = 'border border-primary/10 rounded-xl p-3.5 bg-primary/[0.02]';

                if (komp.tipe === 'fixed') {
                    div.innerHTML = `
                        <p class="text-xs font-bold text-body mb-3">${komp.nama_komponen}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            ${komp.opsi.map(o => {
                                const imgHtml = o.menu.foto 
                                    ? '<img src="/storage/' + o.menu.foto + '" alt="' + o.menu.nama + '" class="w-full aspect-[4/3] object-cover">'
                                    : '<div class="w-full aspect-[4/3] bg-canvas flex items-center justify-center text-body/20"><i class="ph-light ph-image text-4xl"></i></div>';
                                
                                return `
                                <div class="flex flex-col bg-surface rounded-xl border border-primary/20 overflow-hidden relative shadow-sm ring-1 ring-primary/10">
                                    <div class="absolute top-2.5 right-2.5 bg-primary text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center z-10 shadow-sm ring-2 ring-white">
                                        <i class="ph-bold ph-check"></i>
                                    </div>
                                    ${imgHtml}
                                    <div class="p-3 bg-primary/5 border-t border-primary/10">
                                        <span class="block text-sm font-bold text-primary truncate">${o.menu.nama}</span>
                                    </div>
                                </div>`;
                            }).join('')}
                        </div>`;
                } else {
                    div.innerHTML = `
                        <p class="text-xs font-bold text-body mb-3">${komp.nama_komponen} <span class="text-warning font-medium text-xs">(pilih 1)</span></p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            ${komp.opsi.map(o => {
                                const imgHtml = o.menu.foto 
                                    ? '<img src="/storage/' + o.menu.foto + '" alt="' + o.menu.nama + '" class="w-full aspect-[4/3] object-cover group-hover:opacity-90 transition-opacity">'
                                    : '<div class="w-full aspect-[4/3] bg-canvas flex items-center justify-center text-body/20"><i class="ph-light ph-image text-4xl"></i></div>';
                                
                                return `
                                <label class="cursor-pointer group relative">
                                    <input type="radio" name="komponen[${komp.id}]" value="${o.menu.id}" class="opacity-0 absolute w-0 h-0 peer" required>
                                    <div class="flex flex-col bg-surface rounded-xl border border-primary/10 overflow-hidden peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary transition-all duration-200 group-hover:border-primary/50 shadow-sm relative">
                                        <div class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full border-2 border-white/50 bg-black/20 flex items-center justify-center opacity-0 peer-checked:opacity-100 peer-checked:border-white peer-checked:bg-primary transition-all z-10 shadow-sm backdrop-blur-sm">
                                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                                        </div>
                                        ${imgHtml}
                                        <div class="p-3 border-t border-primary/5 peer-checked:bg-primary/5 transition-colors">
                                            <span class="block text-sm font-bold text-body peer-checked:text-primary truncate">${o.menu.nama}</span>
                                        </div>
                                    </div>
                                </label>`;
                            }).join('')}
                        </div>`;
                }
                container.appendChild(div);
            });

            container.querySelectorAll('input').forEach(el => {
                el.addEventListener('change', () => { checkFormValidity(); updateStepper(); });
            });
        } catch (e) {
            console.error(e);
            container.innerHTML = '<p class="text-danger text-xs font-medium">Gagal memuat komponen menu. Coba lagi.</p>';
        }
    }

    /* ============================================================
       FORMAT & PEMBAYARAN
       ============================================================ */
    function formatRp(n) {
        return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    }

    function updatePaymentLabel(val) {
        const label = document.getElementById('label-payment');
        const amount = document.getElementById('dp-amount');
        if (val === 'lunas') {
            label.innerHTML = 'Bayar Lunas <span class="text-amber-700/70 text-[10px] font-normal">(100%)</span>';
            amount.textContent = formatRp(currentTotal);
        } else {
            label.innerHTML = `DP Pembayaran <span class="text-amber-700/70 text-[10px] font-normal">(${cfg.dpPersen}%)</span>`;
            amount.textContent = formatRp(currentDp);
        }

        document.querySelectorAll('input[name="opsi_pembayaran"]').forEach(el => {
            const parent = el.closest('label');
            if (el.checked) {
                parent.classList.add('border-primary', 'bg-primary/5');
                parent.classList.remove('border-primary/10', 'bg-surface');
            } else {
                parent.classList.remove('border-primary', 'bg-primary/5');
                parent.classList.add('border-primary/10', 'bg-surface');
            }
        });
    }

    /* ============================================================
       RINGKASAN & TOTAL
       ============================================================ */
    async function hitungTotalPreview() {
        const porsiInput = document.getElementById('jumlahPorsi') || document.getElementById('jumlahBox');
        const porsi = porsiInput ? (parseInt(porsiInput.value) || 0) : 0;
        const subtotalMenu = hargaSatuan * porsi;

        document.getElementById('subtotal-menu').textContent = formatRp(subtotalMenu);
        document.getElementById('summary-porsi').textContent = porsi > 0 ? porsi + ' ' + cfg.satuanLabel : '0 ' + cfg.satuanLabel;

        const tglAcara = document.getElementById('tanggalAcara').value;
        if (tglAcara) {
            const dateObj = new Date(tglAcara);
            document.getElementById('summary-tgl-acara').textContent = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

            const batas = new Date(dateObj);
            batas.setDate(batas.getDate() - cfg.batasHari);
            document.getElementById('summary-batas-pelunasan').textContent = batas.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }
        document.getElementById('summary-jam-acara').textContent = document.getElementById('jamAcara').value || '-';
        document.getElementById('summary-jam-kirim').textContent = document.getElementById('jamPengambilan').value || '-';

        if (!selectedPaketId) return;

        try {
            const body = {
                paket_id: selectedPaketId,
                [cfg.qtyField]: porsi,
                metode_pengiriman: metodePengiriman,
                jarak_km: jarakKm
            };
            if (cfg.type === 'catering') body.layanan_tambahan = [];

            const res = await fetch(cfg.previewUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(body)
            });
            const data = await res.json();

            if (res.ok) {
                if (metodePengiriman === 'delivery') {
                    document.getElementById('summary-jarak-row').style.display = 'flex';
                    document.getElementById('summary-alamat-row').style.display = 'flex';
                    document.getElementById('summary-jarak').textContent = jarakKm ? jarakKm.toFixed(2) + ' km' : '0 km';
                    document.getElementById('summary-alamat').textContent = document.getElementById('alamatDelivery').value || '-';
                    document.getElementById('summary-ongkir-label').textContent = 'Biaya Pengiriman';
                    document.getElementById('summary-metode').textContent = 'Diantar (Delivery)';
                    document.getElementById('summary-jam-kirim-label').textContent = 'Jam Pengiriman';

                    if (cfg.hasGratisOngkir) {
                        document.getElementById('summary-ongkir').textContent = formatRp(data.ongkir || 0);
                        if (data.ongkir_normal && data.ongkir < data.ongkir_normal) {
                            document.getElementById('summary-ongkir-coret').textContent = formatRp(data.ongkir_normal);
                            document.getElementById('summary-ongkir-coret').classList.remove('hidden');
                            document.getElementById('badge-gratis-ongkir').classList.remove('hidden');
                            document.getElementById('badge-gratis-ongkir').textContent = data.ongkir === 0 ? 'Gratis Ongkir' : 'Diskon Ongkir';
                        } else {
                            document.getElementById('summary-ongkir-coret').classList.add('hidden');
                            document.getElementById('badge-gratis-ongkir').classList.add('hidden');
                        }
                    } else {
                        document.getElementById('summary-ongkir-coret').classList.add('hidden');
                        document.getElementById('badge-gratis-ongkir').classList.add('hidden');
                        document.getElementById('summary-ongkir').textContent = formatRp(data.ongkir || 0);
                    }
                } else {
                    document.getElementById('summary-jarak-row').style.display = 'none';
                    document.getElementById('summary-alamat-row').style.display = 'none';
                    document.getElementById('summary-ongkir-label').textContent = 'Metode';
                    document.getElementById('summary-ongkir').textContent = 'Diambil';
                    document.getElementById('summary-metode').textContent = 'Diambil (Pickup)';
                    document.getElementById('summary-jam-kirim-label').textContent = 'Jam Ambil';
                    document.getElementById('summary-ongkir-coret').classList.add('hidden');
                    document.getElementById('badge-gratis-ongkir').classList.add('hidden');
                }

                document.getElementById('total-tagihan').textContent = formatRp(data.total);
                const totalBigEl = document.getElementById('total-tagihan-big');
                if (totalBigEl) totalBigEl.textContent = formatRp(data.total);
                currentTotal = data.total;
                currentDp = data.dp;

                document.getElementById('summary-sisa-pelunasan').textContent = formatRp(data.total - data.dp);

                const opsiBayarEl = document.querySelector('input[name="opsi_pembayaran"]:checked');
                const opsiBayar = opsiBayarEl ? opsiBayarEl.value : 'dp';
                updatePaymentLabel(opsiBayar);

                if (opsiBayar === 'lunas') {
                    document.getElementById('sisa-pelunasan-container').style.display = 'none';
                    document.getElementById('submitBtn').textContent = 'Bayar';
                } else {
                    document.getElementById('sisa-pelunasan-container').style.display = 'flex';
                    document.getElementById('submitBtn').textContent = 'Bayar';
                }

                checkFormValidity();
                updateStepper();
            } else {
                notifyError(data.error || "Gagal menghitung tagihan.");
                document.getElementById('submitBtn').disabled = true;
            }
        } catch (e) {
            console.error(e);
        }
    }

    /* ============================================================
       EVENT LISTENER Fungsi
       ============================================================ */
    const tanggalEl = document.getElementById('tanggalAcara');
    const qtyEl = document.getElementById('jumlahPorsi') || document.getElementById('jumlahBox');

    if (qtyEl) {
        qtyEl.addEventListener('input', function () {
            const warn = document.getElementById('jumlah-warning');
            const jumlah = parseInt(this.value) || 0;
            if (jumlah > 0 && jumlah < cfg.minPorsi) {
                warn.classList.remove('hidden');
                warn.textContent = cfg.minWarning;
            } else {
                warn.classList.add('hidden');
            }
            hitungTotalPreview();
        });
    }

    if (tanggalEl) {
        tanggalEl.addEventListener('change', function () {
            const warn = document.getElementById('tanggal-warning');
            if (this.value && this.value < minDate) {
                warn.classList.remove('hidden');
            } else {
                warn.classList.add('hidden');
            }
            hitungTotalPreview();
        });
    }

    document.getElementById('jamAcara').addEventListener('input', hitungTotalPreview);
    document.getElementById('jamPengambilan').addEventListener('input', hitungTotalPreview);
    document.getElementById('alamatDelivery').addEventListener('input', hitungTotalPreview);
    document.querySelectorAll('input[name="opsi_pembayaran"]').forEach(el => el.addEventListener('change', hitungTotalPreview));

    function checkFormValidity() {
        const form = document.getElementById(cfg.formId);
        const submitBtn = document.getElementById('submitBtn');

        let isValid = form.checkValidity();

        const p = qtyEl ? (parseInt(qtyEl.value) || 0) : 0;
        if (p < cfg.minPorsi) isValid = false;
        if (!selectedPaketId) isValid = false;

        if (metodePengiriman === 'delivery') {
            const alamat = document.getElementById('alamatDelivery').value;
            if (!alamat || alamat.trim() === '') isValid = false;
        }

        submitBtn.disabled = !isValid;
        updateStepper();
    }

    document.querySelectorAll('#' + cfg.formId + ' input, #' + cfg.formId + ' select, #' + cfg.formId + ' textarea').forEach(el => {
        el.addEventListener('input', checkFormValidity);
        el.addEventListener('change', checkFormValidity);
    });

    /* ============================================================
       STEPPER PROGRESS
       ============================================================ */
    function val(name) {
        return (document.getElementsByName(name)[0] && document.getElementsByName(name)[0].value) || (document.getElementById(name) && document.getElementById(name).value) || '';
    }

    function stepDone(i) {
        if (i === 1) return !!val('nama_pemesan') && !!val('kontak');
        if (i === 2) {
            const alamatOk = metodePengiriman === 'pickup' || (metodePengiriman === 'delivery' && !!val('alamatDelivery'));
            const qty = parseInt(qtyEl ? qtyEl.value : 0) || 0;
            return !!val('tanggal_acara') && !!val('jam_acara') && qty >= cfg.minPorsi && alamatOk;
        }
        if (i === 3) return selectedPaketId !== null;
        if (i === 4) {
            if (selectedPaketId === null) return false;
            const reqs = document.querySelectorAll('#komponen-container input[type="radio"][required]');
            if (reqs.length === 0) return true;
            const names = [...new Set(Array.from(reqs).map(r => r.name))];
            return names.every(name => document.querySelector(`input[name="${name}"]:checked`));
        }
        if (i === 5) {
            const opsi = document.querySelector('input[name="opsi_pembayaran"]:checked');
            return !!opsi && currentTotal > 0;
        }
        return false;
    }

    const stepHints = {
        1: 'Langkah 1 — Isi data pemesan agar admin dapat menghubungi Anda.',
        2: 'Langkah 2 — Atur tanggal, jam, jumlah, dan metode pengambilan/pengiriman.',
        3: 'Langkah 3 — Pilih paket ' + cfg.satuanLabel + ' yang sesuai kebutuhan.',
        4: 'Langkah 4 — Tentukan pilihan menu pada setiap komponen.',
        5: 'Langkah 5 — Pilih cara pembayaran, lalu lanjutkan ke pembayaran.'
    };

    function updateStepper() {
        const items = document.querySelectorAll('.step-item');
        if (!items.length) return;

        let current = 1;
        for (let i = 1; i <= 5; i++) {
            if (!stepDone(i)) { current = i; break; }
            current = i;
        }
        if (current > 5) current = 5;

        items.forEach(item => {
            const n = parseInt(item.dataset.step, 10);
            const dot = item.querySelector('.step-dot');
            const label = item.querySelector('.step-label');

            item.classList.toggle('is-done', n < current);
            item.classList.toggle('is-current', n === current);

            if (dot) {
                dot.classList.toggle('bg-primary', n <= current);
                dot.classList.toggle('bg-surface', n > current); // Fix conflict
                dot.classList.toggle('border-primary', n <= current);
                dot.classList.toggle('text-white', n <= current);
                dot.classList.toggle('ring-4', n === current);
                dot.classList.toggle('ring-primary/15', n === current);
                dot.classList.toggle('border-primary/20', n > current);
                dot.classList.toggle('text-body/40', n > current);

                const num = dot.querySelector('.step-num');
                const check = dot.querySelector('.step-check');
                if (num) num.classList.toggle('hidden', n < current);
                if (check) check.classList.toggle('hidden', n >= current);
            }

            if (label) {
                label.classList.toggle('text-primary', n < current);
                label.classList.toggle('text-body', n === current);
                label.classList.toggle('text-body/50', n > current);
            }
        });

        const hint = document.getElementById('stepper-hint');
        if (hint) hint.textContent = stepHints[current] || '';
    }

    /* ============================================================
       AUTO-SELECT PAKET DARI URL (?paket_id=)
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const paketId = urlParams.get('paket_id');
        if (paketId) {
            const paketCard = document.querySelector(`.paket-card[data-paket-id="${paketId}"]`);
            if (paketCard) paketCard.click();
        }
        checkFormValidity();
        updateStepper();
    });
</script>