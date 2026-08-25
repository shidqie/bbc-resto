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
            html: '<div class="w-8 h-8 bg-white border border-primary/10 shadow-sm rounded-full flex items-center justify-center text-primary"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>',
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
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin inline-block text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>';
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
    function setMetodePengiriman(metode) {
        metodePengiriman = metode;

        document.querySelectorAll('.metode-card').forEach(c => {
            const radio = c.querySelector('.metode-radio');
            if (radio) {
                radio.checked = (radio.value === metode);
                const selected = radio.checked;
                c.classList.toggle('border-primary', selected);
                c.classList.toggle('bg-primary/5', selected);
                c.classList.toggle('ring-1', selected);
                c.classList.toggle('ring-primary', selected);
                c.classList.toggle('border-primary/10', !selected);
                c.classList.toggle('bg-surface', !selected);
            }
        });

        const delSection = document.getElementById('deliverySection');
        const alamatInput = document.getElementById('alamatDelivery');
        if (metode === 'delivery') {
            if (delSection) delSection.classList.remove('hidden');
            if (alamatInput) {
                alamatInput.required = true;
                alamatInput.name = 'lokasi_acara';
            }
            setTimeout(initMap, 200);
        } else {
            if (delSection) delSection.classList.add('hidden');
            if (alamatInput) {
                alamatInput.required = false;
                alamatInput.name = '';
            }
        }
        saveOrderDraft();
        hitungTotalPreview();
        checkFormValidity();
        updateStepper();
    }

    document.querySelectorAll('.metode-radio').forEach(r => {
        r.addEventListener('change', (e) => {
            setMetodePengiriman(e.target.value);
        });
    });

    /* ============================================================
       PILIH PAKET
       ============================================================ */
    function selectPaket(paketId, customKomponen = null) {
        const card = document.querySelector(`.paket-card[data-paket-id="${paketId}"]`);
        if (!card) return;

        document.querySelectorAll('.paket-card').forEach(c => {
            c.classList.remove('border-primary', 'bg-primary/5', 'ring-1', 'ring-primary');
            c.classList.add('border-primary/10', 'bg-surface');
            const ind = c.querySelector('.selected-indicator');
            if (ind) ind.style.opacity = '0';
        });

        card.classList.add('border-primary', 'bg-primary/5', 'ring-1', 'ring-primary');
        card.classList.remove('border-primary/10', 'bg-surface');
        const ind = card.querySelector('.selected-indicator');
        if (ind) ind.style.opacity = '1';

        const radio = card.querySelector('.paket-radio');
        if (radio) radio.checked = true;

        selectedPaketId = card.dataset.paketId;
        hargaSatuan = parseInt(card.dataset.harga) || 0;

        const pName = card.querySelector('div h3') ? card.querySelector('div h3').textContent : 'Paket Terpilih';
        const sumPaket = document.getElementById('summary-paket');
        if (sumPaket) sumPaket.textContent = pName;

        loadKomponen(selectedPaketId, customKomponen);
        saveOrderDraft();
        hitungTotalPreview();
    }

    document.querySelectorAll('.paket-card').forEach(card => {
        card.addEventListener('click', () => {
            selectPaket(card.dataset.paketId);
        });
    });
    const preloadedKomponen = (cfg && cfg.komponenMap) ? cfg.komponenMap : {};

    function renderKomponenDOM(komponens, selectedChoices = null) {
        const container = document.getElementById('komponen-container');
        if (!container) return;

        if (!Array.isArray(komponens) || komponens.length === 0) {
            container.innerHTML = '<p class="text-body/50 text-xs py-2">Tidak ada pilihan komponen tambahan untuk paket ini.</p>';
            checkFormValidity();
            updateStepper();
            return;
        }

        function resolveMenuImgUrl(foto) {
            if (!foto) return null;
            if (foto.startsWith('http://') || foto.startsWith('https://') || foto.startsWith('data:')) return foto;
            if (foto.startsWith('/')) return foto;
            if (foto.startsWith('images/')) return '/' + foto;
            return '/storage/' + foto;
        }

        container.innerHTML = '';
        komponens.forEach(komp => {
            const div = document.createElement('div');
            div.className = 'border border-primary/10 rounded-xl p-3.5 bg-primary/[0.02]';
            const opsis = Array.isArray(komp.opsi) ? komp.opsi : [];

            if (komp.tipe === 'fixed') {
                div.innerHTML = `
                    <p class="text-xs font-bold text-body mb-3">${komp.nama_komponen || 'Komponen'}</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        ${opsis.map(o => {
                            const m = o.menu || o;
                            const nama = m.nama || m.nama_pilihan || m.nama_menu || 'Menu';
                            const foto = m.foto || o.foto || null;
                            const imgUrl = resolveMenuImgUrl(foto);
                            const imgHtml = imgUrl 
                                ? `<img src="${imgUrl}" alt="${nama}" class="w-full aspect-[4/3] object-cover" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='/storage/${foto || ''}';}">`
                                : `<div class="w-full aspect-[4/3] bg-canvas flex items-center justify-center text-body/20"><svg class="w-8 h-8 text-body/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>`;
                            
                            return `
                            <div class="flex flex-col bg-surface rounded-xl border border-primary/20 overflow-hidden relative shadow-sm ring-1 ring-primary/10">
                                <div class="absolute top-2.5 right-2.5 bg-primary text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center z-10 shadow-sm ring-2 ring-white">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                ${imgHtml}
                                <div class="p-3 bg-primary/5 border-t border-primary/10">
                                    <span class="block text-sm font-bold text-primary truncate">${nama}</span>
                                </div>
                            </div>`;
                        }).join('')}
                    </div>`;
            } else {
                const savedVal = selectedChoices ? (selectedChoices[komp.id] || selectedChoices[String(komp.id)]) : null;
                div.innerHTML = `
                    <p class="text-xs font-bold text-body mb-3">${komp.nama_komponen || 'Komponen'} <span class="text-warning font-medium text-xs">(pilih 1)</span></p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        ${opsis.map((o, idx) => {
                            const m = o.menu || o;
                            const menuId = m.id || o.id;
                            const nama = m.nama || m.nama_pilihan || m.nama_menu || 'Menu';
                            const foto = m.foto || o.foto || null;
                            const isChecked = (savedVal !== null && savedVal !== undefined) ? (String(savedVal) === String(menuId)) : (opsis.length === 1);
                            const imgUrl = resolveMenuImgUrl(foto);
                            const imgHtml = imgUrl 
                                ? `<img src="${imgUrl}" alt="${nama}" class="w-full aspect-[4/3] object-cover group-hover:opacity-90 transition-opacity" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='/storage/${foto || ''}';}">`
                                : `<div class="w-full aspect-[4/3] bg-canvas flex items-center justify-center text-body/20"><svg class="w-8 h-8 text-body/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>`;
                            
                            return `
                            <label class="cursor-pointer group relative">
                                <input type="radio" name="komponen[${komp.id}]" value="${menuId}" ${isChecked ? 'checked' : ''} class="opacity-0 absolute w-0 h-0 peer" required>
                                <div class="flex flex-col bg-surface rounded-xl border border-primary/10 overflow-hidden peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary transition-all duration-200 group-hover:border-primary/50 shadow-sm relative">
                                    <div class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full border-2 border-white/50 bg-black/20 flex items-center justify-center opacity-0 peer-checked:opacity-100 peer-checked:border-white peer-checked:bg-primary transition-all z-10 shadow-sm backdrop-blur-sm">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    ${imgHtml}
                                    <div class="p-3 border-t border-primary/5 peer-checked:bg-primary/5 transition-colors">
                                        <span class="block text-sm font-bold text-body peer-checked:text-primary truncate">${nama}</span>
                                    </div>
                                </div>
                            </label>`;
                        }).join('')}
                    </div>`;
            }
            container.appendChild(div);
        });

        container.querySelectorAll('input').forEach(el => {
            el.addEventListener('change', () => { 
                saveOrderDraft();
                checkFormValidity(); 
                updateStepper(); 
            });
        });

        checkFormValidity();
        updateStepper();
    }

    async function loadKomponen(paketId, selectedChoices = null) {
        if (!paketId || paketId === 'null' || paketId === 'undefined' || isNaN(parseInt(paketId))) {
            const sec = document.getElementById('sec-komponen');
            if (sec) sec.classList.add('hidden');
            return;
        }

        const sec = document.getElementById('sec-komponen');
        const container = document.getElementById('komponen-container');
        if (!sec || !container) return;

        sec.classList.remove('hidden');

        // Check preloaded cache first (0 network latency, 100% reliable)
        let komponens = preloadedKomponen[paketId] || preloadedKomponen[String(paketId)] || null;

        if (komponens && Array.isArray(komponens) && komponens.length > 0) {
            renderKomponenDOM(komponens, selectedChoices);
            return;
        }

        container.innerHTML = `
            <div class="flex items-center gap-2 py-4 text-body/50 text-xs font-medium">
                <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memuat komponen menu...</span>
            </div>
        `;

        try {
            const cleanPath = window.location.pathname.replace(/\/+$/, '');
            const candidateUrls = [
                cfg.komponenUrl ? cfg.komponenUrl.replace(':id', encodeURIComponent(paketId)).replace('%3Aid', encodeURIComponent(paketId)) : null,
                `${cleanPath}/komponen/${encodeURIComponent(paketId)}`,
                `/pesan/nasi-box/komponen/${encodeURIComponent(paketId)}`,
                `/pesan/catering/komponen/${encodeURIComponent(paketId)}`
            ].filter(Boolean);

            const uniqueUrls = [...new Set(candidateUrls)];
            let lastError = null;

            for (const url of uniqueUrls) {
                try {
                    const res = await fetch(url, {
                        headers: { 
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (Array.isArray(data) && data.length > 0) {
                            komponens = data;
                            break;
                        }
                    }
                } catch (err) {
                    lastError = err;
                }
            }

            if (!komponens) {
                throw lastError || new Error('Gagal memuat komponen menu dari server.');
            }

            renderKomponenDOM(komponens, selectedChoices);
        } catch (e) {
            console.error('Error loadKomponen:', e);
            container.innerHTML = `
                <div class="p-4 bg-danger/5 border border-danger/20 rounded-xl flex items-center justify-between gap-3">
                    <p class="text-danger text-xs font-medium">Gagal memuat pilihan menu untuk paket ini.</p>
                    <button type="button" onclick="loadKomponen('${paketId}')" class="px-3 py-1 bg-danger text-white text-xs font-bold rounded-lg hover:bg-danger/90 transition-colors">
                        Coba Lagi
                    </button>
                </div>
            `;
        }
    }

    /* ============================================================
       FORMAT & PEMBAYARAN
       ============================================================ */
    function formatRp(n) {
        return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    }

    function setOpsiPembayaran(val) {
        document.querySelectorAll('input[name="opsi_pembayaran"]').forEach(el => {
            el.checked = (el.value === val);
            const parent = el.closest('label');
            if (parent) {
                const isSelected = (el.value === val);
                parent.classList.toggle('border-primary', isSelected);
                parent.classList.toggle('bg-primary/5', isSelected);
                parent.classList.toggle('ring-1', isSelected);
                parent.classList.toggle('ring-primary', isSelected);
                parent.classList.toggle('border-primary/10', !isSelected);
                parent.classList.toggle('bg-surface', !isSelected);

                const outerDot = parent.querySelector('.radio-dot');
                const innerDot = parent.querySelector('.radio-dot span');
                if (outerDot) {
                    outerDot.classList.toggle('border-primary', isSelected);
                    outerDot.classList.toggle('border-body/20', !isSelected);
                }
                if (innerDot) {
                    innerDot.classList.toggle('bg-primary', isSelected);
                    innerDot.classList.toggle('bg-transparent', !isSelected);
                }
            }
        });

        const label = document.getElementById('label-payment');
        const amount = document.getElementById('dp-amount');
        if (label && amount) {
            if (val === 'lunas') {
                label.innerHTML = 'Bayar Lunas <span class="text-amber-700/70 text-[10px] font-normal">(100%)</span>';
                amount.textContent = formatRp(currentTotal);
            } else {
                label.innerHTML = `DP Pembayaran <span class="text-amber-700/70 text-[10px] font-normal">(${cfg.dpPersen}%)</span>`;
                amount.textContent = formatRp(currentDp);
            }
        }

        const sisaContainer = document.getElementById('sisa-pelunasan-container');
        if (sisaContainer) {
            sisaContainer.style.display = (val === 'lunas') ? 'none' : 'flex';
        }

        saveOrderDraft();
        checkFormValidity();
        updateStepper();
    }

    function updatePaymentLabel(val) {
        setOpsiPembayaran(val);
    }

    document.querySelectorAll('.metode-bayar-card').forEach(card => {
        card.addEventListener('click', function (e) {
            const radio = this.querySelector('input[name="opsi_pembayaran"]');
            if (radio) {
                setOpsiPembayaran(radio.value);
            }
        });
    });

    document.querySelectorAll('input[name="opsi_pembayaran"]').forEach(el => {
        el.addEventListener('change', function () {
            setOpsiPembayaran(this.value);
        });
    });

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

        const opsiBayarEl = document.querySelector('input[name="opsi_pembayaran"]:checked');
        const opsiBayar = opsiBayarEl ? opsiBayarEl.value : 'dp';

        if (!selectedPaketId) {
            currentTotal = subtotalMenu;
            currentDp = Math.round(subtotalMenu * (cfg.dpPersen / 100));
            document.getElementById('total-tagihan').textContent = formatRp(currentTotal);
            const totalBigEl = document.getElementById('total-tagihan-big');
            if (totalBigEl) totalBigEl.textContent = formatRp(currentTotal);
            document.getElementById('summary-sisa-pelunasan').textContent = formatRp(currentTotal - currentDp);
            setOpsiPembayaran(opsiBayar);
            checkFormValidity();
            updateStepper();
            return;
        }

        try {
            const body = {
                paket_id: selectedPaketId,
                [cfg.qtyField]: porsi,
                metode_pengiriman: metodePengiriman,
                jarak_km: jarakKm
            };
            if (cfg.type === 'catering') body.layanan_tambahan = [];

            const cleanPath = window.location.pathname.replace(/\/+$/, '');
            const candidateUrls = [
                cfg.previewUrl,
                `${cleanPath}/preview`,
                `/pesan/${cfg.type === 'nasibox' ? 'nasi-box' : 'catering'}/preview`,
                `/pesan/catering/preview`
            ].filter(Boolean);

            const uniqueUrls = [...new Set(candidateUrls)];
            let res = null;
            let data = null;

            for (const url of uniqueUrls) {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(body)
                    });
                    if (response.ok) {
                        res = response;
                        data = await response.json();
                        break;
                    }
                } catch (err) {
                    console.warn('Failed preview fetch on ' + url, err);
                }
            }

            if (res && res.ok && data) {
                if (metodePengiriman === 'delivery') {
                    document.getElementById('summary-jarak-row').style.display = 'flex';
                    document.getElementById('summary-alamat-row').style.display = 'flex';
                    document.getElementById('summary-jarak').textContent = jarakKm ? jarakKm.toFixed(2) + ' km' : '0 km';
                    document.getElementById('summary-alamat').textContent = document.getElementById('alamatDelivery').value || '-';
                    document.getElementById('summary-ongkir-label').textContent = 'Biaya Pengiriman';
                    document.getElementById('summary-metode').textContent = 'Diantar';
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
                    document.getElementById('summary-metode').textContent = 'Diambil di Resto';
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

                setOpsiPembayaran(opsiBayar);

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

    /* ============================================================
       DRAFT PERSISTENCE (AUTO-SAVE & RESTORE)
       ============================================================ */
    const DRAFT_KEY = 'bbc_order_draft_' + (cfg.type || 'catering');

    function saveOrderDraft() {
        try {
            const form = document.getElementById(cfg.formId);
            if (!form) return;

            const komponenChoices = {};
            document.querySelectorAll('#komponen-container input[type="radio"]:checked').forEach(r => {
                const match = r.name.match(/komponen\[(\d+)\]/);
                if (match) {
                    komponenChoices[match[1]] = r.value;
                }
            });

            const draft = {
                nama_pemesan: document.getElementById('nama_pemesan')?.value || '',
                kontak: document.getElementById('kontak')?.value || '',
                tanggal_acara: document.getElementById('tanggalAcara')?.value || '',
                jam_acara: document.getElementById('jamAcara')?.value || '',
                qty: (document.getElementById('jumlahPorsi') || document.getElementById('jumlahBox'))?.value || '',
                metode_pengiriman: document.querySelector('input[name="metode_pengiriman"]:checked')?.value || 'pickup',
                jam_pengambilan: document.getElementById('jamPengambilan')?.value || '',
                lokasi_acara: document.getElementById('alamatDelivery')?.value || '',
                alamat_venue: document.getElementById('alamatVenue')?.value || '',
                latitude: document.getElementById('inputLat')?.value || '',
                longitude: document.getElementById('inputLng')?.value || '',
                jarak_km: document.getElementById('inputJarak')?.value || '',
                paket_id: selectedPaketId || document.querySelector('input[name="paket_id"]:checked')?.value || '',
                komponen: komponenChoices,
                catatan: document.getElementById('catatan')?.value || '',
                opsi_pembayaran: document.querySelector('input[name="opsi_pembayaran"]:checked')?.value || 'dp',
            };

            sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
        } catch (e) {
            console.warn('Gagal menyimpan draft pemesanan:', e);
        }
    }

    function getSavedDraft() {
        let draft = null;
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            if (raw) draft = JSON.parse(raw);
        } catch (e) {}

        const oldCfg = cfg.old || {};

        return {
            nama_pemesan: oldCfg.nama_pemesan || draft?.nama_pemesan || '',
            kontak: oldCfg.kontak || draft?.kontak || '',
            tanggal_acara: oldCfg.tanggal_acara || draft?.tanggal_acara || '',
            jam_acara: oldCfg.jam_acara || draft?.jam_acara || '',
            qty: oldCfg.jumlah_porsi || oldCfg.jumlah_box || draft?.qty || '',
            metode_pengiriman: oldCfg.metode_pengiriman || draft?.metode_pengiriman || 'pickup',
            jam_pengambilan: oldCfg.jam_pengambilan || draft?.jam_pengambilan || '',
            lokasi_acara: oldCfg.lokasi_acara || draft?.lokasi_acara || '',
            alamat_venue: oldCfg.alamat_venue || draft?.alamat_venue || '',
            latitude: oldCfg.latitude || draft?.latitude || '',
            longitude: oldCfg.longitude || draft?.longitude || '',
            jarak_km: oldCfg.jarak_km || draft?.jarak_km || '',
            paket_id: oldCfg.paket_id || draft?.paket_id || '',
            komponen: (oldCfg.komponen && Object.keys(oldCfg.komponen).length > 0) ? oldCfg.komponen : (draft?.komponen || {}),
            catatan: oldCfg.catatan || draft?.catatan || '',
            opsi_pembayaran: oldCfg.opsi_pembayaran || draft?.opsi_pembayaran || 'dp',
        };
    }

    function restoreOrderDraft() {
        const data = getSavedDraft();
        const urlParams = new URLSearchParams(window.location.search);

        const namaEl = document.getElementById('nama_pemesan');
        if (namaEl && data.nama_pemesan && !namaEl.value) namaEl.value = data.nama_pemesan;

        const kontakEl = document.getElementById('kontak');
        if (kontakEl && data.kontak && !kontakEl.value) kontakEl.value = data.kontak;

        const tglEl = document.getElementById('tanggalAcara');
        if (tglEl && data.tanggal_acara) tglEl.value = data.tanggal_acara;

        const jamAcaraEl = document.getElementById('jamAcara');
        if (jamAcaraEl && data.jam_acara) jamAcaraEl.value = data.jam_acara;

        const porsiEl = document.getElementById('jumlahPorsi') || document.getElementById('jumlahBox');
        if (porsiEl && data.qty) porsiEl.value = data.qty;

        const jamKirimEl = document.getElementById('jamPengambilan');
        if (jamKirimEl && data.jam_pengambilan) jamKirimEl.value = data.jam_pengambilan;

        const venueEl = document.getElementById('alamatVenue');
        if (venueEl && data.alamat_venue) venueEl.value = data.alamat_venue;

        const alamatEl = document.getElementById('alamatDelivery');
        if (alamatEl && data.lokasi_acara) {
            alamatEl.value = data.lokasi_acara;
            const cardAlamat = document.getElementById('cardAlamat');
            if (cardAlamat) cardAlamat.textContent = data.lokasi_acara;
        }

        const latEl = document.getElementById('inputLat');
        if (latEl && data.latitude) latEl.value = data.latitude;

        const lngEl = document.getElementById('inputLng');
        if (lngEl && data.longitude) lngEl.value = data.longitude;

        const jarakEl = document.getElementById('inputJarak');
        if (jarakEl && data.jarak_km) {
            jarakEl.value = data.jarak_km;
            jarakKm = parseFloat(data.jarak_km) || 0;
        }

        const catatanEl = document.getElementById('catatan');
        if (catatanEl && data.catatan) catatanEl.value = data.catatan;

        if (data.metode_pengiriman) {
            setMetodePengiriman(data.metode_pengiriman);
        }

        if (data.opsi_pembayaran) {
            const bayarRadio = document.querySelector(`input[name="opsi_pembayaran"][value="${data.opsi_pembayaran}"]`);
            if (bayarRadio) {
                bayarRadio.checked = true;
                updatePaymentLabel(data.opsi_pembayaran);
            }
        }

        const targetPaketId = urlParams.get('paket_id') || data.paket_id;
        if (targetPaketId && targetPaketId !== 'null' && targetPaketId !== 'undefined' && !isNaN(parseInt(targetPaketId))) {
            const card = document.querySelector(`.paket-card[data-paket-id="${targetPaketId}"]`);
            if (card) {
                selectPaket(targetPaketId, data.komponen);
            }
        }

        hitungTotalPreview();
        checkFormValidity();
        updateStepper();
    }

    /* ============================================================
       AUTO-SAVE ON ANY INPUT
       ============================================================ */
    document.querySelectorAll('#' + cfg.formId + ' input, #' + cfg.formId + ' select, #' + cfg.formId + ' textarea').forEach(el => {
        el.addEventListener('input', () => {
            saveOrderDraft();
            checkFormValidity();
        });
        el.addEventListener('change', () => {
            saveOrderDraft();
            checkFormValidity();
        });
    });

    const orderForm = document.getElementById(cfg.formId);
    if (orderForm) {
        orderForm.addEventListener('submit', function () {
            sessionStorage.removeItem(DRAFT_KEY);
        });
    }

    /* ============================================================
       AUTO-RESTORE ON LOAD
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        restoreOrderDraft();
    });
</script>