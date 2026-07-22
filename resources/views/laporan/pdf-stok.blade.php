<!DOCTYPE html>
<html>
<head>
    <title>Laporan Persediaan Bahan Baku</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            color: #0F2E23; /* Hijau BBC Resto */
        }
        .header p {
            margin: 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SAUNG BABAKAN CINTA (BBC RESTO)</h2>
        <p>Laporan Persediaan & Mutasi Bahan Baku</p>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Waktu</th>
                <th width="20%">Bahan Baku</th>
                <th width="15%">Jenis Mutasi</th>
                <th width="30%">Keterangan</th>
                <th width="15%" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mutasis as $index => $mutasi)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $mutasi->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $mutasi->bahanBaku->nama_bahan }}</td>
                <td>{{ ucfirst($mutasi->jenis_mutasi) }}</td>
                <td>{{ $mutasi->keterangan }}</td>
                <td class="text-right">
                    @if($mutasi->jumlah > 0)
                        +{{ $mutasi->jumlah }}
                    @else
                        {{ $mutasi->jumlah }}
                    @endif
                    {{ $mutasi->bahanBaku->satuan->nama_satuan }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada pergerakan stok</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
