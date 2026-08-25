<!DOCTYPE html>
<html>
<head>
    <title>Laporan Persediaan Bahan Baku</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap');
        * { font-family: 'Outfit', sans-serif !important; }
        body { font-family: 'Outfit', sans-serif !important; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; }
        .header h3 { margin: 0 0 5px 0; font-size: 14px; font-weight: normal; }
        .header p { margin: 0; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 7px 10px; text-align: left; font-size: 11px; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-aman { color: #16a34a; font-weight: bold; }
        .badge-menipis { color: #d97706; font-weight: bold; }
        .badge-habis { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RUMAH MAKAN SAUNG BABAKAN CINTA</h2>
        <h3>LAPORAN PERSEDIAAN BAHAN BAKU</h3>
        <p>Dicetak Pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 80px;">Kode</th>
                <th>Nama Bahan Baku</th>
                <th class="text-center" style="width: 60px;">Satuan</th>
                <th class="text-right" style="width: 90px;">Stok Saat Ini</th>
                <th class="text-right" style="width: 90px;">Stok Minimum</th>
                <th class="text-center" style="width: 80px;">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanBahan as $index => $item)
            @php
                $condClass = $item['status'] == 'Aman' ? 'badge-aman' : ($item['status'] == 'Menipis' ? 'badge-menipis' : 'badge-habis');
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item['id_bahan_baku'] ?? '-' }}</td>
                <td>{{ $item['nama_bahan'] }}</td>
                <td class="text-center">{{ $item['satuan'] }}</td>
                <td class="text-right">{{ \App\Helpers\UnitHelper::formatQuantity($item['stok_saat_ini'], $item['satuan']) }}</td>
                <td class="text-right">{{ \App\Helpers\UnitHelper::formatQuantity($item['stok_minimum'], $item['satuan']) }}</td>
                <td class="text-center {{ $condClass }}">{{ $item['status'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Data persediaan belum tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
