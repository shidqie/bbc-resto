<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Penjualan</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1f2937; margin: 0; padding: 20px; }
  .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; }
  .header h1 { font-size: 16px; font-weight: 700; margin: 0 0 4px; }
  .header p  { font-size: 9px; color: #6b7280; margin: 2px 0; }
  .stats-grid { display: flex; gap: 12px; margin-bottom: 16px; }
  .stat-card  { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; }
  .stat-label { font-size: 8px; color: #6b7280; margin-bottom: 3px; }
  .stat-value { font-size: 13px; font-weight: 700; }
  table { width: 100%; border-collapse: collapse; }
  thead tr { background: #f9fafb; }
  th { padding: 7px 8px; text-align: left; font-size: 8px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb; }
  td { padding: 6px 8px; font-size: 8.5px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
  tr:nth-child(even) td { background: #f9fafb; }
  .badge { padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 7.5px; }
  .badge-dinein   { background: #eff6ff; color: #1d4ed8; }
  .badge-catering { background: #f5f3ff; color: #6d28d9; }
  .badge-nasibox  { background: #fff7ed; color: #c2410c; }
  .badge-lunas    { background: #ecfdf5; color: #065f46; }
  .badge-dp       { background: #fffbeb; color: #92400e; }
  .badge-belum    { background: #fef2f2; color: #991b1b; }
  .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; display: flex; justify-content: space-between; }
  .text-right { text-align: right; }
</style>
</head>
<body>

<div class="header">
    <h1>Laporan Penjualan</h1>
    <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} — {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
    @if($jenisPenjualan) <p>Jenis: {{ ucfirst($jenisPenjualan) }}</p> @endif
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Transaksi</div>
        <div class="stat-value">{{ number_format($pesanans->count()) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Penjualan</div>
        <div class="stat-value" style="color:#1d4ed8">Rp {{ number_format($stats['totalPenjualan'], 0, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pembayaran Masuk</div>
        <div class="stat-value" style="color:#065f46">Rp {{ number_format($stats['totalDibayar'], 0, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Piutang</div>
        <div class="stat-value" style="color:#92400e">Rp {{ number_format($stats['totalPiutang'], 0, ',', '.') }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:30px">No</th>
            <th>ID Pesanan</th>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Pelanggan / Meja</th>
            <th class="text-right">Total Tagihan</th>
            <th class="text-right">Dibayar</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pesanans as $i => $pesanan)
        @php
            $totalDibayarRow = $pesanan->pembayaran->sum('jumlah_bayar');
            $kodeStatus = $pesanan->pembayaran->first()?->status_pembayaran?->kode_status ?? 'MENUNGGU';
            $namaStatus = $pesanan->pembayaran->first()?->status_pembayaran?->nama_status ?? 'Menunggu';
            $badgeStatus = match($kodeStatus) {
                'LUNAS'    => 'badge-lunas',
                'SEBAGIAN' => 'badge-dp',
                default    => 'badge-belum',
            };
            $jenis      = $pesanan->jenis_pesanan?->kode_jenis ?? 'DIN';
            $jenisLabel = $pesanan->jenis_pesanan?->nama_jenis ?? 'Dine In';
            $badgeJenis = match($jenis) {
                'CAT' => 'badge-catering',
                'BOX' => 'badge-nasibox',
                default => 'badge-dinein',
            };
            $pelangganLabel = $pesanan->pelanggan?->nama
                ?? ($pesanan->meja ? 'Meja ' . $pesanan->meja->nomor_meja : $pesanan->nomor_pesanan);
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $pesanan->nomor_pesanan ?? '#'.$pesanan->id }}</td>
            <td>{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d/m/Y') }}</td>
            <td><span class="badge {{ $badgeJenis }}">{{ $jenisLabel }}</span></td>
            <td>{{ $pelangganLabel }}</td>
            <td class="text-right">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($totalDibayarRow, 0, ',', '.') }}</td>
            <td><span class="badge {{ $badgeStatus }}">{{ $namaStatus }}</span></td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center; color:#9ca3af; padding:20px">Tidak ada data transaksi.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <span>Dicetak oleh: {{ $cetakOleh }}</span>
    <span>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>
