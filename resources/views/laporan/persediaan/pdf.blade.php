<!DOCTYPE html>
<html>
<head>
    <title>Laporan Persediaan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PERSEDIAAN BAHAN BAKU</h2>
        <p>Per Tanggal: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Kode Bahan</th>
                <th>Nama Bahan</th>
                <th>Kategori</th>
                <th>Jenis Stok</th>
                <th class="text-right">Stok Saat Ini</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporanBahan as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ explode('_', $item['id'])[0] }}</td>
                <td>{{ $item['nama_bahan'] }}</td>
                <td>{{ $item['kategori'] ?? '—' }}</td>
                <td>{{ $item['jenis_stok'] }}</td>
                <td class="text-right">{{ number_format($item['stok_saat_ini'], 2, ',', '.') }} {{ $item['satuan'] }}</td>
                <td class="text-center">{{ $item['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
