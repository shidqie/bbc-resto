<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Scan QR Code Meja - Saung Babakan Cinta</title>
    <meta name="description" content="Pindai QR Code di meja Anda untuk melihat menu digital & langsung memesan dari smartphone.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Anonymous+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Google Sans"', 'sans-serif'],
                        mono: ['"Anonymous Pro"', 'monospace'],
                    },
                    fontSize: {
                        xs: ['13px', '1.45'],
                        sm: ['15px', '1.5'],
                        base: ['16px', '1.55'],
                        lg: ['18px', '1.5'],
                        xl: ['20px', '1.4'],
                        '2xl': ['24px', '1.3'],
                        '3xl': ['30px', '1.25'],
                        '4xl': ['36px', '1.2'],
                        '5xl': ['48px', '1.15'],
                        '6xl': ['60px', '1.1'],
                    },
                    colors: {
                        brand:   '#0D3024',
                        primary: '#3B82F6',
                        accent:  '#D4A843',
                        surface: '#FFFFFF',
                        text:    '#111827',
                    }
                }
            }
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar{display:none}
        .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
        [x-cloak]{display:none!important}
        .mono{font-family:'Anonymous Pro',monospace;letter-spacing:.04em}

        .scan-laser {
            position: absolute;
            left: 5%;
            right: 5%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #D4A843, #10B981, #D4A843, transparent);
            box-shadow: 0 0 15px #10B981;
            animation: scanAnim 2s infinite ease-in-out;
            z-index: 10;
        }

        @keyframes scanAnim {
            0% { top: 10%; opacity: 0.3; }
            50% { top: 85%; opacity: 1; }
            100% { top: 10%; opacity: 0.3; }
        }

        #qr-reader video {
            border-radius: 1.5rem !important;
            object-fit: cover !important;
            width: 100% !important;
        }

        #qr-reader {
            border: none !important;
            background: transparent !important;
        }

        #qr-reader__dashboard {
            padding: 8px !important;
        }
    </style>
</head>

<body class="bg-[#0D3024] text-white min-h-screen font-sans flex flex-col justify-between selection:bg-emerald-500 selection:text-white" x-data="qrScannerPage()" x-cloak>

    <!-- Header Section -->
    <header class="p-5 flex items-center justify-between border-b border-white/10 bg-black/20 backdrop-blur-md">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/15 border border-white/20 flex items-center justify-center text-amber-400 shadow-md">
                <x-heroicon-o-qr-code class="w-6 h-6" />
            </div>
            <div>
                <h1 class="text-base font-extrabold text-white tracking-tight leading-none">Pindai QR Meja</h1>
                <p class="text-xs text-emerald-200/80 mt-0.5 font-medium leading-none">Saung Babakan Cinta</p>
            </div>
        </div>

        <a href="{{ route('qr.menu') }}" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-bold text-white transition flex items-center gap-2">
            <x-heroicon-o-sparkles class="text-amber-400 w-5 h-5" /> Menu
        </a>
    </header>

    <!-- Main Scanner Body -->
    <main class="flex-1 max-w-md w-full mx-auto p-4 flex flex-col justify-center items-center text-center">

        <!-- Title instructions -->
        <div class="mb-4 space-y-1">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold">
                <x-heroicon-o-camera class="w-3 h-3" /> Kamera Aktif
            </span>
            <h2 class="text-xl font-black text-white tracking-tight">Arahkan Kamera ke QR Code Meja</h2>
            <p class="text-xs text-emerald-100/70 font-medium px-4">
                Posisikan QR code yang ada di stiker/akrilik meja di dalam area kotak untuk otomatis membuka menu digital.
            </p>
        </div>

        <!-- Camera Viewport Box -->
        <div class="relative w-full max-w-[320px] aspect-square rounded-xl bg-black/40 border-2 border-emerald-400/40 p-2 shadow-2xl overflow-hidden flex items-center justify-center">
            
            <!-- Laser Animation -->
            <div class="scan-laser" x-show="isScanning"></div>

            <!-- Corner Frame Accents -->
            <div class="absolute top-4 left-4 w-6 h-6 border-t-4 border-l-4 border-amber-400 rounded-tl-3xl z-20 pointer-events-none"></div>
            <div class="absolute top-4 right-4 w-6 h-6 border-t-4 border-r-4 border-amber-400 rounded-tr-3xl z-20 pointer-events-none"></div>
            <div class="absolute bottom-4 left-4 w-6 h-6 border-b-4 border-l-4 border-amber-400 rounded-bl-3xl z-20 pointer-events-none"></div>
            <div class="absolute bottom-4 right-4 w-6 h-6 border-b-4 border-r-4 border-amber-400 rounded-br-3xl z-20 pointer-events-none"></div>

            <!-- HTML5 QR Reader Container -->
            <div id="qr-reader" class="w-full h-full"></div>

            <!-- Loading overlay when camera is starting -->
            <div x-show="loading" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center gap-3 z-30">
                <x-heroicon-o-arrow-path class="animate-spin text-amber-400 w-10 h-10" />
                <p class="text-xs font-bold text-white">Menyiapkan Kamera...</p>
            </div>
        </div>

        <!-- Manual Upload or Table Pick Options -->
        <div class="mt-6 w-full max-w-[320px] space-y-3">

            <!-- File Upload Option -->
            <label class="w-full h-11 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                <x-heroicon-o-photo class="text-emerald-300 w-5 h-5" />
                <span>Unggah Foto QR Code</span>
                <input type="file" id="qr-input-file" accept="image/*" class="hidden" @change="handleFileUpload($event)">
            </label>

            <!-- Manual Table Pick Modal Trigger -->
            <button @click="showTableModal = true" class="w-full h-11 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/40 text-amber-300 text-sm font-bold transition flex items-center justify-center gap-2">
                <x-heroicon-o-list-bullet class="w-4 h-4" />
                <span>Atau Pilih Nomor Meja Manually</span>
            </button>
        </div>

    </main>

    <!-- Modal Manual Pick Table -->
    <div x-show="showTableModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showTableModal = false"></div>
        <div class="relative bg-white text-gray-900 w-full max-w-sm rounded-xl p-6 shadow-2xl border border-gray-100 z-10 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#0D3024]">Pilih Nomor Meja</h3>
                    <p class="text-xs text-gray-500 font-medium">Klik meja tempat Anda duduk</p>
                </div>
                <button @click="showTableModal = false" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-sm hover:bg-gray-200 transition">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="grid grid-cols-3 gap-2.5 max-h-60 overflow-y-auto p-1">
                @foreach($mejas as $m)
                @php $cleanNomor = trim(preg_replace('/^meja\s*/i', '', $m->nomor_meja)); @endphp
                <a href="{{ route('qr.menu', ['meja' => $m->id]) }}" 
                   class="bg-emerald-50/60 hover:bg-[#0D3024] hover:text-white border border-emerald-200/80 rounded-xl p-3 text-center transition-all group">
                    <span class="block text-xs uppercase font-bold text-gray-400 group-hover:text-emerald-300">Meja</span>
                    <span class="block text-base font-black text-[#0D3024] group-hover:text-white mt-0.5">{{ $cleanNomor }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="p-4 text-center border-t border-white/10 bg-black/20 text-xs text-emerald-200/60 font-medium">
        &copy; {{ date('Y') }} Saung Babakan Cinta &bull; Resto & Catering POS System
    </footer>

    <!-- Alpine & Camera JS Handler -->
    <script>
    function qrScannerPage() {
        return {
            loading: true,
            isScanning: true,
            showTableModal: false,
            html5QrCode: null,

            init() {
                this.$nextTick(() => {
                    this.startScanner();
                });
            },

            startScanner() {
                this.html5QrCode = new Html5Qrcode("qr-reader");
                const config = { fps: 10, qrbox: { width: 220, height: 220 } };

                this.html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText, decodedResult) => {
                        this.onQrCodeSuccess(decodedText);
                    },
                    (errorMessage) => {
                        // ignore continuous frame read errors
                    }
                ).then(() => {
                    this.loading = false;
                }).catch((err) => {
                    console.warn("Camera start failed, fallbacking to default camera:", err);
                    Html5Qrcode.getCameras().then(devices => {
                        if (devices && devices.length) {
                            const cameraId = devices[0].id;
                            this.html5QrCode.start(cameraId, config, (decodedText) => {
                                this.onQrCodeSuccess(decodedText);
                            }).then(() => { this.loading = false; });
                        } else {
                            this.loading = false;
                        }
                    }).catch(() => {
                        this.loading = false;
                    });
                });
            },

            onQrCodeSuccess(decodedText) {
                if (navigator.vibrate) navigator.vibrate(100);

                if (this.html5QrCode) {
                    this.html5QrCode.stop().catch(() => {});
                }

                if (decodedText.includes('qr-menu') || decodedText.includes('meja') || decodedText.startsWith('http')) {
                    window.location.href = decodedText;
                } else {
                    window.location.href = "{{ route('qr.menu') }}?meja=" + encodeURIComponent(decodedText);
                }
            },

            handleFileUpload(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (!this.html5QrCode) {
                    this.html5QrCode = new Html5Qrcode("qr-reader");
                }

                this.loading = true;
                this.html5QrCode.scanFile(file, true)
                    .then(decodedText => {
                        this.onQrCodeSuccess(decodedText);
                    })
                    .catch(err => {
                        this.loading = false;
                        window.showToast('info', "Tidak dapat membaca QR code dari gambar ini. Pastikan gambar jelas.");
                    });
            }
        };
    }
    </script>

    <x-toast />
</body>
</html>
