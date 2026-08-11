<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Persediaan Bahan Baku</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1f2937; margin: 0; padding: 20px; }
  .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; }
  .header h1 { font-size: 16px; font-weight: 700; margin: 0 0 4px; }
  .header p  { font-size: 9px; color: #6b7280; margin: 2px 0; }
  table { width: 100%; border-collapse: collapse; }
  thead tr { background: #f3f4f6; }
  th { padding: 7px 8px; text-align: left; font-size: 8px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb; }
  td { padding: 6px 8px; font-size: 8.5px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
  tr:nth-child(even) td { background: #f9fafb; }
  .text-right { text-align: right; }
  .masuk  { color: #065f46; font-weight: 700; }
  .keluar { color: #991b1b; font-weight: 700; }
  .akhir  { font-weight: 700; color: #111827; }
  .badge  { padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 7.5px; }
  .badge-aman    { background: #ecfdf5; color: #065f46; }
  .badge-minimum { background: #fffbeb; color: #92400e; }
  .badge-habis   { background: #fef2f2; color: #991b1b; }
  .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; display: flex; justify-content: space-between; }
</style>
</head>
<body>

<div class="header">
    <h1>Laporan Persediaan Bahan Baku</h1>
    <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} — {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
    @if($jenisPersediaan) <p>Jenis: {{ ucfirst(strtolower($jenisPersediaan)) }}</p> @endif
</div>

<table>
    <thead>
        <tr>
            <th style="width:28px">No</th>
            <th>Kode</th>
            <th>Nama Bahan</th>
            <th>Kategori</th>
            <th class="text-right">Stok Awal</th>
            <th class="text-right">Masuk</th>
            <th class="text-right">Keluar</th>
            <th class="text-right">Penyesuaian</th>
            <th class="text-right">Stok Akhir</th>
            <th>Satuan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($laporanBahan as $i => $item)
        @php
            $bahan  = $item['bahan'];
            $status = $item['status'];
            $badgeClass = match($status) {
                'Aman'    => 'badge-aman',
                'Minimum' => 'badge-minimum',
                'Habis'   => 'badge-habis',
                default   => '',
            };
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $bahan->id_bahan_baku ?? '-' }}</td>
            <td>{{ $bahan->nama_bahan }}</td>
            <td>{{ $bahan->kategori_bahan_baku?->nama_kategori ?? '-' }}</td>
            <td class="text-right">{{ number_format($item['stok_awal'] ?? $item['stokAwal'] ?? 0, 2) }}</td>
            <td class="text-right masuk">+{{ number_format($item['stok_masuk'] ?? $item['stokMasuk'] ?? 0, 2) }}</td>
            <td class="text-right keluar">-{{ number_format($item['stok_keluar'] ?? $item['stokKeluar'] ?? 0, 2) }}</td>
            <td class="text-right">{{ number_format($item['penyesuaian'] ?? 0, 2) }}</td>
            <td class="text-right akhir">{{ number_format($item['stok_akhir'] ?? $item['stokAkhir'] ?? 0, 2) }}</td>
            <td>{{ $bahan->satuan?->singkatan ?? '-' }}</td>
            <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td>
        </tr>
        @empty
        <tr><td colspan="11" style="text-align:center; color:#9ca3af; padding:20px">Tidak ada data pada periode yang dipilih.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <span>Dicetak oleh: {{ $cetakOleh }}</span>
    <span>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>
