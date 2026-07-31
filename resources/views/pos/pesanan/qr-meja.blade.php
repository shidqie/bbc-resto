<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mengunduh QR Code...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- HTML2PDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        #pdf-content {
            /* This content must be visible in the normal DOM flow for html2canvas to render it perfectly! */
            /* We will hide it behind the loading overlay instead of using display: none or left: -9999px */
            width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .qr-page-container {
            /* Ensures it doesn't split a single card in half */
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: always;
            break-after: page;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 80px 0; /* Memberikan ruang atas-bawah yang rapi tanpa memaksakan tinggi mutlak */
        }

        .qr-page-container:last-child {
            /* Menghindari halaman kosong ekstra di akhir dokumen */
            page-break-after: auto;
            break-after: auto;
        }

        /* Full Screen Loading Overlay */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999; /* Cover everything */
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
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body>
    
    <!-- Loading Screen (This is what the user sees while processing) -->
    <div id="loading-screen">
        <div class="loader mb-4"></div>
        <h2 class="text-2xl font-bold text-gray-800">Menyiapkan File PDF...</h2>
        <p class="text-gray-500 mt-2 text-sm">Mohon tunggu sebentar, file sedang di-render resolusi tinggi.</p>
        <p class="text-gray-400 mt-1 text-xs" id="status-text">Otomatis terunduh saat selesai...</p>
    </div>

    <!-- Content to convert to PDF -->
    <div id="pdf-content">
        @php 
            $logoPath = public_path('images/logo-saung.png');
            $logoSrc = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : '';
        @endphp
        
        @forelse($mejas as $m)
            @php
                $qrTargetUrl = !empty(trim($m->qr_token)) ? route('qr.menu', ['token' => trim($m->qr_token)]) : url('/qr-menu?invalid');
                $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=0&data=" . urlencode($qrTargetUrl);
                
                // Ambil gambar dari API dan ubah ke Base64 agar html2canvas tidak terblokir CORS!
                try {
                    $qrImageContext = stream_context_create(['http' => ['timeout' => 5]]);
                    $qrImageData = file_get_contents($qrApiUrl, false, $qrImageContext);
                    $qrSrc = $qrImageData ? 'data:image/png;base64,'.base64_encode($qrImageData) : '';
                } catch (\Exception $e) {
                    $qrSrc = '';
                }

                $cleanNomorMeja = trim(preg_replace('/^meja\s*/i', '', $m->nomor_meja));
            @endphp
            
            <div class="qr-page-container w-full flex justify-center">
                <!-- Kartu QR EXACTLY AS USER PROVIDED -->
                <div class="w-[300px] aspect-[1/1.55] rounded-3xl overflow-hidden shadow-xl border-4 border-emerald-500/30 flex flex-col justify-between p-5 relative text-white" style="background: linear-gradient(145deg, #0F2E23 0%, #164032 50%, #0A2219 100%); width: 300px; height: 465px;">
                    <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/40 pointer-events-none"></div>
                    <div class="absolute top-3 left-3 w-4 h-4 border-t-2 border-l-2 border-amber-400/60 rounded-tl-lg"></div>
                    <div class="absolute top-3 right-3 w-4 h-4 border-t-2 border-r-2 border-amber-400/60 rounded-tr-lg"></div>
                    <div class="absolute bottom-3 left-3 w-4 h-4 border-b-2 border-l-2 border-amber-400/60 rounded-bl-lg"></div>
                    <div class="absolute bottom-3 right-3 w-4 h-4 border-b-2 border-r-2 border-amber-400/60 rounded-br-lg"></div>

                    <div class="relative z-10 text-center pt-1 space-y-0.5">
                        <h2 class="text-2xl font-black uppercase tracking-wider text-amber-400 drop-shadow-md leading-none">SCAN MENU</h2>
                        <div class="pt-2">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md text-white border border-amber-400/40 text-[12px] font-extrabold shadow-sm">
                                <x-heroicon-o-users class="w-3 h-3 text-amber-400" /> <span>Meja {{ $cleanNomorMeja }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="relative z-10 my-auto py-1 flex flex-col items-center">
                        <div class="bg-white rounded-3xl p-3.5 shadow-2xl border-4 border-amber-400/50 relative flex items-center justify-center">
                            @if($qrSrc)
                            <img src="{{ $qrSrc }}" alt="QR Code" class="w-44 h-44 object-contain rounded-xl">
                            @else
                            <div class="w-44 h-44 bg-gray-200 rounded-xl flex items-center justify-center text-gray-400 text-xs text-center p-2">Gagal memuat QR</div>
                            @endif
                            @if($logoSrc)
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="w-11 h-11 rounded-full bg-white p-1 shadow-xl border-2 border-emerald-800 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $logoSrc }}" alt="Logo" class="w-full h-full object-contain">
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="mt-3 text-center">
                            <p class="text-[11px] font-bold text-white tracking-wide">Scan QR Code untuk pesan sendiri</p>
                            <p class="text-[9px] font-medium text-amber-300 mt-0.5">Arahkan kamera HP Anda</p>
                        </div>
                    </div>

                    <div class="relative z-10 text-center pb-1 pt-1.5 border-t border-amber-400/30 flex items-center justify-center gap-2">
                        @if($logoSrc)
                        <div class="w-7 h-7 rounded-full bg-white/10 backdrop-blur-md p-1 flex items-center justify-center border border-amber-400/40 shrink-0">
                            <img src="{{ $logoSrc }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        @endif
                        <div class="text-left">
                            <h3 class="text-[11px] font-black tracking-wider text-white uppercase leading-none">SAUNG BABAKAN CINTA</h3>
                            <span class="text-[8px] font-semibold text-amber-300 block leading-tight mt-0.5">Rumah Makan Khas Sunda</span>
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
        window.onload = function() {
            setTimeout(function() {
                document.getElementById('status-text').innerText = "Memproses halaman (ini mungkin butuh beberapa detik)...";
                
                var elements = document.querySelectorAll('.qr-page-container');
                if(elements.length === 0) {
                    window.close();
                    return;
                }

                var opt = {
                    margin:       0,
                    filename:     'QR_Code_Meja.pdf',
                    image:        { type: 'jpeg', quality: 1.0 },
                    html2canvas:  { 
                        scale: 2, // Scale 2 cukup tajam dan hemat memori
                        useCORS: true,
                        logging: false
                    },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                // Membangun PDF per halaman untuk menghindari batas memori Canvas (Blank Putih)
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
            }, 1000); 
        }
    </script>
</body>
</html>
