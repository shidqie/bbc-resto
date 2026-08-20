@extends('pdf.layout')

@section('title', 'Surat Pesanan Pembelian - ' . $po->nomor_po)

@section('content')
    <div class="doc-header">
        <div class="company-name">RUMAH MAKAN SAUNG BABAKAN CINTA</div>
        <div class="doc-title">SURAT PESANAN PEMBELIAN</div>
        <div class="doc-subtitle">(PURCHASE ORDER) &bull; NO: {{ $po->nomor_po }}</div>
        <div class="header-divider"></div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%; padding-right: 15px;">
                <table>
                    <tr>
                        <td class="info-label">Nomor PO</td>
                        <td class="info-colon">:</td>
                        <td class="info-val font-bold font-mono">{{ $po->nomor_po }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal PO</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ \Carbon\Carbon::parse($po->tanggal_po)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Kebutuhan</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ \Carbon\Carbon::parse($po->tanggal_kebutuhan ?? date('Y-m-d', strtotime($po->tanggal_po . ' +1 day')))->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding-left: 15px;">
                <table>
                    <tr>
                        <td class="info-label">Supplier</td>
                        <td class="info-colon">:</td>
                        <td class="info-val font-bold">{{ $po->supplier }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">WhatsApp Supplier</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ $po->no_telp_supplier ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Alamat Supplier</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ $po->alamat_supplier ?? 'Garut, Jawa Barat' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="pdf-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">No</th>
                <th>Nama Bahan Baku</th>
                <th class="text-right" style="width: 85px;">Jumlah</th>
                <th class="text-center" style="width: 70px;">Satuan</th>
                <th class="text-right" style="width: 120px;">Harga Satuan</th>
                <th class="text-right" style="width: 130px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($po->detail_purchase_order as $idx => $d)
            @php
                $rawQty = (float) $d->jumlah_dipesan;
                $satuanRaw = optional(optional($d->bahan_baku)->satuan)->singkatan ?? optional(optional($d->bahan_baku)->satuan)->nama_satuan ?? 'gram';
                $satuanClean = strtolower(trim($satuanRaw));

                $displayQty = $rawQty;
                $displaySatuan = $satuanRaw;

                if (in_array($satuanClean, ['gram', 'g', 'gr', 'kilogram', 'kg']) && $rawQty >= 1000) {
                    $displayQty = $rawQty / 1000;
                    $displaySatuan = 'kg';
                } elseif (in_array($satuanClean, ['ml', 'mililiter', 'liter', 'l']) && $rawQty >= 1000) {
                    $displayQty = $rawQty / 1000;
                    $displaySatuan = 'liter';
                }

                $total = (float) $d->harga_satuan;
                if ($total <= 0) $total = (float) optional($d->detail_pengadaan_bahan)->harga_satuan;
                if ($total <= 0) $total = (float) optional($d->bahan_baku)->harga_satuan;
                if ($total <= 0) $total = 16000;
                if ($total < 100) $total = 16000;

                $hargaPerUnit = $displayQty > 0 ? ($total / $displayQty) : $total;
                $grandTotal += $total;

                $namaBahan = optional($d->bahan_baku)->nama_bahan ?? 'Bahan Baku #' . $d->bahan_baku_id;
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $namaBahan }}</strong></td>
                <td class="text-right font-bold">{{ \App\Helpers\UnitHelper::formatNumber($displayQty) }}</td>
                <td class="text-center">{{ $displaySatuan }}</td>
                <td class="text-right font-mono">Rp {{ number_format($hargaPerUnit, 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold">
                <td colspan="5" class="text-right">Total Pembelian:</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($po->catatan)
        <div class="notes-text">
            <strong>Catatan PO:</strong> {{ $po->catatan }}
        </div>
    @endif
@endsection
