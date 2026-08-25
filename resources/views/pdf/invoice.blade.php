@include('pelanggan.pembayaran.invoice-pdf', [
    'pesanan' => $pesanan,
    'type' => $type ?? (match ($pesanan->jenis_pesanan_id) { 2 => 'catering', 3 => 'nasi_box', default => 'dine_in' }),
    'kodePesanan' => $pesanan->id_pesanan,
    'namaPemesan' => optional($pesanan->pelanggan)->nama ?? optional($pesanan->jadwal_pesanan)->nama_penerima ?? '-',
    'kontak' => optional($pesanan->pelanggan)->nomor_telepon ?? optional($pesanan->jadwal_pesanan)->nomor_telepon_penerima ?? '-',
    'isPdf' => true
])
