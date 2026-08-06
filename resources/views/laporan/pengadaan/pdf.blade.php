<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengadaan</title>
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
        <h2>LAPORAN PENGADAAN BAHAN BAKU</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nomor Permintaan</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Pemasok</th>
                <th>Status</th>
                <th>Diajukan Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengadaans as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $p->nomor_pengadaan }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_pengadaan)->format('d/m/Y') }}</td>
                <td>{{ ucfirst($p->jenis_pengadaan) }}</td>
                <td>{{ optional($p->pemasok)->nama_pemasok ?? '—' }}</td>
                <td>{{ optional($p->status_pengadaan)->nama_status ?? '—' }}</td>
                <td>{{ optional($p->diajukan_oleh_pengguna)->nama ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
