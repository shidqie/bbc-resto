@extends('pdf.layout')

@section('title', 'Invoice Pemesanan - ' . $pesanan->id_pesanan)

@section('content')
    <div class="doc-header">
        <div class="company-name">RUMAH MAKAN SAUNG BABAKAN CINTA</div>
        <div class="doc-title">INVOICE / BUKTI PEMESANAN</div>
        <div class="doc-subtitle">KODE PESANAN: {{ $pesanan->id_pesanan }}</div>
        <div class="header-divider"></div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%; padding-right: 15px;">
                <table>
                    <tr>
                        <td class="info-label">Kode Pesanan</td>
                        <td class="info-colon">:</td>
                        <td class="info-val font-bold font-mono">{{ $pesanan->id_pesanan }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Pesanan</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Jenis Pesanan</td>
                        <td class="info-colon">:</td>
                        <td class="info-val font-bold">{{ optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Pesanan' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Pengiriman</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ $pesanan->tanggal_pengiriman ? \Carbon\Carbon::parse($pesanan->tanggal_pengiriman)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding-left: 15px;">
                <table>
                    <tr>
                        <td class="info-label">Nama Pelanggan</td>
                        <td class="info-colon">:</td>
                        <td class="info-val font-bold">{{ optional($pesanan->pelanggan)->nama ?? 'Umum' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">No. Telepon</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ optional($pesanan->pelanggan)->nomor_telepon ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Alamat Pengiriman</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ optional($pesanan->pelanggan)->alamat ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="pdf-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">No</th>
                <th>Menu / Paket</th>
                <th class="text-center" style="width: 80px;">Jumlah</th>
                <th class="text-right" style="width: 120px;">Harga</th>
                <th class="text-right" style="width: 130px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotalTotal = 0; @endphp
            @foreach($pesanan->detail_pesanan as $idx => $detail)
            @php
                $qty = (float) $detail->jumlah;
                $harga = (float) $detail->harga_satuan;
                $subtotal = $qty * $harga;
                $subtotalTotal += $subtotal;
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ optional($detail->menu)->nama_menu ?? '-' }}</strong></td>
                <td class="text-center">{{ $qty }} porsi</td>
                <td class="text-right font-mono">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold">
                <td colspan="4" class="text-right">Total Tagihan:</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
