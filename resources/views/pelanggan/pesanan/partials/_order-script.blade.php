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
            html: '<div class="w-8 h-8 bg-white border border-primary/10 shadow-sm rounded-full flex items-center justify-center text-primary"><i class="ph ph-storefront text-lg"></i></div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            tooltipAnchor: [0, -20]
        });
        const userIcon = L.divIcon({
            html: '<div class="w-8 h-8 bg-primary shadow-md shadow-primary/30 rounded-full flex items-center justify-center text-white"><i class="ph-bold ph-map-pin text-lg"></i></div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            tooltipAnchor: [0, -20]
        });

        restoMarker = L.marker([bbcLat, bbcLng], { icon: restoIcon }).addTo(map);
        restoMarker.bindTooltip("Saung Babakan Cinta", { permanent: true, direction: 'top', offset: [0, -10], className: 'resto-tooltip' }).openTooltip();

        let initLat = document.getElementById('inputLat').value || bbcLat;
        let initLng = document.getElementById('inputLng').value || bbcLng;
        marker = L.marker([initLat, initLng], { icon: userIcon, draggable: true }).addTo(map);
        marker.bindTooltip("Alamatmu di sini", { permanent: true, direction: 'top', offset: [0, -10], className: 'address-tooltip' }).openTooltip();

        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            document.getElementById('inputLat').value = pos.lat;
            document.getElementById('inputLng').value = pos.lng;
            hitungJarakOSRM(bbcLat, bbcLng, pos.lat, pos.lng);
            updateAlamatText(pos.lat, pos.lng);
        });

        if (document.getElementById('inputLat').value) {
            map.setView([initLat, initLng], 14);
            hitungJarakOSRM(bbcLat, bbcLng, initLat, initLng);
        } else {
            locateUser(false);
        }

        const geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            placeholder: 'Cari lokasi atau alamat...',
            collapsed: false,
            position: 'topright'
        }).addTo(map);

        geocoder.on('markgeocode', function (e) {
            const bbox = e.geocode.bbox;
            const poly = L.polygon([
                bbox.getSouthEast(),
                bbox.getNorthEast(),
                bbox.getNorthWest(),
                bbox.getSouthWest()
            ]);
            map.fitBounds(poly.getBounds());

            marker.setLatLng(e.geocode.center);

            const pos = marker.getLatLng();
            document.getElementById('inputLat').value = pos.lat;
            document.getElementById('inputLng').value = pos.lng;
            hitungJarakOSRM(bbcLat, bbcLng, pos.lat, pos.lng);

            if (e.geocode.name) {
                document.getElementById('cardAlamat').textContent = e.geocode.name;
                document.getElementById('alamatDelivery').value = e.geocode.name;
            }
        });
    }

    function locateUser(showAlert = true) {
        if (showAlert) document.getElementById('cardAlamat').textContent = "Mencari lokasi GPS...";

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;

                marker.setLatLng([userLat, userLng]);
                map.setView([userLat, userLng], 15);

                document.getElementById('inputLat').value = userLat;
                document.getElementById('inputLng').value = userLng;

                hitungJarakOSRM(bbcLat, bbcLng, userLat, userLng);
                updateAlamatText(userLat, userLng);
            }, function () {
                console.log("Geolocation error");
                document.getElementById('cardAlamat').textContent = "Geser pin ke lokasi kamu...";
            }, { enableHighAccuracy: true });
        } else {
            document.getElementById('cardAlamat').textContent = "Geser pin ke lokasi kamu...";
        }
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
                        <p class="text-xs font-bold text-body mb-2">${komp.nama_komponen}</p>
                        <div class="flex flex-wrap gap-2">
                            ${komp.opsi.map(o => `
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary text-xs rounded-lg font-semibold">
                                    ${o.menu.foto ? `<img src="/storage/${o.menu.foto}" alt="${o.menu.nama}" class="w-4 h-4 rounded-full object-cover">` : ''}
                                    <span>${o.menu.nama} ✓</span>
                                </div>
                            `).join('')}
                        </div>`;
                } else {
                    div.innerHTML = `
                        <p class="text-xs font-bold text-body mb-2">${komp.nama_komponen} <span class="text-warning font-medium text-[11px]">(pilih 1)</span></p>
                        <div class="flex flex-wrap gap-2">
                            ${komp.opsi.map(o => `
                                <label class="cursor-pointer group relative">
                                    <input type="radio" name="komponen[${komp.id}]" value="${o.menu.id}" class="opacity-0 absolute w-0 h-0 peer" required>
                                    <div class="flex items-center gap-1.5 px-3 py-1.5 border border-primary/10 bg-surface rounded-xl font-medium text-body text-xs peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white transition-all duration-200 group-hover:border-primary/50">
                                        ${o.menu.foto ? `<img src="/storage/${o.menu.foto}" alt="${o.menu.nama}" class="w-4 h-4 rounded-full object-cover">` : ''}
                                        <span>${o.menu.nama}</span>
                                    </div>
                                </label>`).join('')}
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
                    document.getElementById('submitBtn').textContent = 'Bayar Lunas ' + formatRp(data.total);
                } else {
                    document.getElementById('sisa-pelunasan-container').style.display = 'flex';
                    document.getElementById('submitBtn').textContent = 'Bayar DP ' + formatRp(data.dp);
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
            const reqs = document.querySelectorAll('#komponen-container input[required]');
            return reqs.length === 0 || Array.from(reqs).every(r => r.checked);
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