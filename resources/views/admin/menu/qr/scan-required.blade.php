<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Scan QR Meja — BBC Resto</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
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
                }
            }
        }
    </script>
    <style>body{font-family:'Plus Jakarta Sans',sans-serif;}</style>
</head>
<body class="bg-[#F8FAFC] min-h-screen flex items-center justify-center p-5">
    <div class="max-w-sm w-full text-center">
        {{-- Logo --}}
        <div class="w-16 h-16 rounded-full bg-[#0D3024] flex items-center justify-center mx-auto mb-6 shadow-xl shadow-[#0D3024]/20">
            <x-heroicon-o-sparkles class="w-7 h-7 text-emerald-300" />
        </div>

        {{-- Ilustrasi QR --}}
        <div class="w-32 h-32 rounded-xl bg-white border-2 border-dashed border-gray-200 flex items-center justify-center mx-auto mb-6 shadow-sm">
            <x-heroicon-o-qr-code class="text-5xl text-gray-200 w-5 h-5" />
        </div>

        <h1 class="text-xl font-extrabold text-gray-900 mb-2">Scan QR Code Meja</h1>
        <p class="text-sm text-gray-500 leading-relaxed mb-6">
            Untuk mulai memesan, silakan <strong class="text-gray-700">scan QR Code</strong> yang ada di meja Anda menggunakan kamera HP.
        </p>

        <div class="bg-white border border-gray-200 rounded-xl p-4 text-left space-y-3 shadow-sm mb-6">
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">1</div>
                <p class="text-xs text-gray-600 mt-0.5">Buka kamera HP Anda dan arahkan ke <strong>QR Code di atas meja</strong></p>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">2</div>
                <p class="text-xs text-gray-600 mt-0.5">Tap tautan yang muncul untuk membuka menu digital</p>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">3</div>
                <p class="text-xs text-gray-600 mt-0.5">Pilih menu dan pesan langsung dari meja Anda</p>
            </div>
        </div>

        <p class="text-xs text-gray-400">Butuh bantuan? Panggil staf kami yang terdekat.</p>
        <p class="text-xs font-bold text-[#0D3024] mt-1">BBC Resto &bull; Saung Babakan Cinta</p>
    </div>
</body>
</html>
