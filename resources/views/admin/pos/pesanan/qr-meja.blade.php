<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mengunduh QR Code...</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-saung.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-saung.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- QRCode.js — Local, works fully offline on LAN -->
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <!-- HTML2PDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        #pdf-content {
            width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .qr-page-container {
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: always;
            break-after: page;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 80px 0;
        }

        .qr-page-container:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        /* Full Screen Loading Overlay */
        #loading-screen {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loader {
            border: 5px solid #e5e7eb;
            border-top-color: #10b981;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }

        .qr-canvas-holder {
            width: 176px;
            height: 176px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-canvas-holder canvas,
        .qr-canvas-holder img {
            width: 176px !important;
            height: 176px !important;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    fontSize: {
                        xs: ['11px', '1.45'], sm: ['13px', '1.5'],
                        base: ['14px', '1.55'], lg: ['16px', '1.5'],
                        xl: ['18px', '1.4'], '2xl': ['21px', '1.3'],
                        '3xl': ['26px', '1.25'], '4xl': ['32px', '1.2'],
                        '5xl': ['40px', '1.15'], '6xl': ['48px', '1.1'],
                    },
                    colors: {
                        primary: '#0D3024',
                        canvas: '#FAFAF7',
                        surface: '#FFFFFF',
                    },
                }
            }
        }
    </script>
</head>
<body>
    
    <!-- Loading Screen -->
    <div id="loading-screen">
        <div class="loader mb-4"></div>
        <h2 class="text-2xl font-bold text-gray-800">Menyiapkan File PDF...</h2>
        <p class="text-gray-500 mt-2 text-sm">Mohon tunggu sebentar, QR Code sedang digenerate.</p>
        <p class="text-gray-400 mt-1 text-xs" id="status-text">Otomatis terunduh saat selesai...</p>
    </div>

    <!-- Content to convert to PDF -->
    <div id="pdf-content">
        @php 
            $logoPath = public_path('images/logo-saung.png');
            $logoSrc = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : '';
        @endphp
        
        @php
            $lanBaseUrl = \App\Helpers\IdCodeGenerator::getLanBaseUrl();
        @endphp
        
        @forelse($mejas as $m)
            @php
                $qrTargetUrl = !empty(trim($m->qr_token)) 
                    ? $lanBaseUrl . '/qr-menu/' . trim($m->qr_token) 
                    : $lanBaseUrl . '/qr-menu?invalid';
                $cleanNomorMeja = trim(preg_replace('/^meja\s*/i', '', $m->nomor_meja));
            @endphp
            
            <div class="qr-page-container w-full flex justify-center">
                <div class="w-[300px] aspect-[1/1.55] rounded-xl overflow-hidden shadow-xl border-4 border-emerald-500/30 flex flex-col justify-between p-5 relative text-white" style="background: linear-gradient(145deg, #0D3024 0%, #164032 50%, #0A2219 100%); width: 300px; height: 465px;">
                    <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/40 pointer-events-none"></div>
                    <div class="absolute top-3 left-3 w-4 h-4 border-t-2 border-l-2 border-amber-400/60 rounded-tl-3xl"></div>
                    <div class="absolute top-3 right-3 w-4 h-4 border-t-2 border-r-2 border-amber-400/60 rounded-tr-3xl"></div>
                    <div class="absolute bottom-3 left-3 w-4 h-4 border-b-2 border-l-2 border-amber-400/60 rounded-bl-3xl"></div>
                    <div class="absolute bottom-3 right-3 w-4 h-4 border-b-2 border-r-2 border-amber-400/60 rounded-br-3xl"></div>

                    <div class="relative z-10 text-center pt-1 space-y-0.5">
                        <h2 class="text-2xl font-black uppercase tracking-wider text-amber-400 drop-shadow-md leading-none">SCAN MENU</h2>
                        <div class="pt-2">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md text-white border border-amber-400/40 text-xs font-extrabold shadow-sm">
                                <span>Meja {{ $cleanNomorMeja }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="relative z-10 my-auto py-1 flex flex-col items-center">
                        <div class="bg-white rounded-xl p-3.5 shadow-2xl border-4 border-amber-400/50 relative flex items-center justify-center">
                            <!-- QR canvas generated locally by qrcode.js -->
                            <div class="qr-canvas-holder" data-qr-url="{{ $qrTargetUrl }}"></div>
                            @if($logoSrc)
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="w-11 h-11 rounded-full bg-white p-1 shadow-xl border-2 border-emerald-800 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $logoSrc }}" alt="Logo" class="w-full h-full object-contain">
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="mt-3 text-center">
                            <p class="text-xs font-bold text-white tracking-wide">Scan QR Code untuk pesan sendiri</p>
                            <p class="text-xs font-medium text-amber-300 mt-0.5">Arahkan kamera HP Anda</p>
                        </div>
                    </div>

                    <div class="relative z-10 text-center pb-1 pt-1.5 border-t border-amber-400/30 flex items-center justify-center gap-2">
                        @if($logoSrc)
                        <div class="w-7 h-7 rounded-full bg-white/10 backdrop-blur-md p-1 flex items-center justify-center border border-amber-400/40 shrink-0">
                            <img src="{{ $logoSrc }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        @endif
                        <div class="text-left">
                            <h3 class="text-xs font-black tracking-wider text-white uppercase leading-none">SAUNG BABAKAN CINTA</h3>
                            <span class="text-xs font-semibold text-amber-300 block leading-tight mt-0.5">Rumah Makan Khas Sunda</span>
                        </div>
                    </div>
                </div>
            </div>
            
        @empty
            <div class="p-12 text-gray-500 font-medium w-full text-center">
                <p>Belum ada data meja atau QR token.</p>
            </div>
        @endforelse
    </div>

    <script>
        // Step 1: Generate all QR codes locally using qrcode.js (no internet needed)
        function generateAllQrCodes() {
            const holders = document.querySelectorAll('.qr-canvas-holder');
            holders.forEach(function(holder) {
                const url = holder.getAttribute('data-qr-url');
                if (url) {
                    new QRCode(holder, {
                        text: url,
                        width: 176,
                        height: 176,
                        colorDark: '#0D3024',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                }
            });
        }

        // Step 2: After QR codes are rendered, export to PDF
        window.onload = function() {
            generateAllQrCodes();

            setTimeout(function() {
                document.getElementById('status-text').innerText = "Memproses halaman (ini mungkin butuh beberapa detik)...";
                
                var elements = document.querySelectorAll('.qr-page-container');
                if (elements.length === 0) {
                    window.close();
                    return;
                }

                var opt = {
                    margin:       0,
                    filename:     'QR_Code_Meja.pdf',
                    image:        { type: 'jpeg', quality: 1.0 },
                    html2canvas:  { 
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        allowTaint: true
                    },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                // Build PDF page by page to avoid canvas memory limits
                var worker = html2pdf().set(opt).from(elements[0]).toPdf();
                
                for (let i = 1; i < elements.length; i++) {
                    worker = worker.get('pdf').then(function(pdf) {
                        pdf.addPage();
                    }).from(elements[i]).toContainer().toCanvas().toPdf();
                }

                worker.save().then(function() {
                    document.getElementById('status-text').innerText = "Selesai! Menutup tab...";
                    document.getElementById('status-text').style.color = "#10b981";
                    setTimeout(function() {
                        window.close();
                    }, 500);
                });
            }, 1500); // beri waktu QR render sebelum capture
        }
    </script>
</body>
</html>
