<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Berhasil - Saung Babakan Cinta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-50 min-h-screen font-sans flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl p-8 text-center space-y-6">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
            <i class="fa-solid fa-check text-3xl text-green-600"></i>
        </div>

        <h2 class="text-2xl font-bold text-gray-900">Pesanan Berhasil Dikirim!</h2>
        <p class="text-gray-500">Nomor pesanan Anda:</p>
        <p class="text-3xl font-bold text-primary">{{ $pesananCatering->no_pesanan }}</p>

        <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Paket:</span><span class="font-semibold">{{ $pesananCatering->paketCatering->nama_paket }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Jumlah Porsi:</span><span class="font-semibold">{{ $pesananCatering->jumlah_porsi }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Total:</span><span class="font-bold">Rp {{ number_format($pesananCatering->total_harga, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">DP ({{ $pesananCatering->dp_percentage }}%):</span><span class="font-bold text-red-600">Rp {{ number_format($pesananCatering->dp_amount, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Tanggal Acara:</span><span class="font-semibold">{{ $pesananCatering->tanggal_acara->format('d F Y') }}</span></div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-left">
            <p class="text-yellow-800 text-sm font-medium"><i class="fa-solid fa-info-circle mr-1"></i> Langkah Selanjutnya:</p>
            <ol class="text-yellow-700 text-sm mt-2 list-decimal list-inside space-y-1">
                <li>Lakukan pembayaran DP sebesar <strong>Rp {{ number_format($pesananCatering->dp_amount, 0, ',', '.') }}</strong></li>
                <li>Transfer ke rekening yang akan dikonfirmasi via WhatsApp</li>
                <li>Pesanan akan dikonfirmasi oleh Admin setelah DP diterima</li>
            </ol>
        </div>

        <a href="{{ route('catering.pesan') }}" class="inline-block px-6 py-2 bg-primary text-white rounded-lg hover:bg-orange-700 transition-colors">
            <i class="fa-solid fa-arrow-left mr-1"></i> Pesan Lagi
        </a>
    </div>
</body>
</html>
