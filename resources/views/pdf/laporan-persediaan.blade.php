@extends('pdf.layout')

@section('title', 'Laporan Persediaan Bahan Baku - RM Saung Babakan Cinta')

@section('content')
    <div class="doc-header">
        <div class="company-name">RUMAH MAKAN SAUNG BABAKAN CINTA</div>
        <div class="doc-title">LAPORAN PERSEDIAAN BAHAN BAKU</div>
        <div class="doc-subtitle">Per Tanggal: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
        <div class="header-divider"></div>
    </div>

    <table class="pdf-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">No</th>
                <th style="width: 90px;">Kode</th>
                <th>Nama Bahan Baku</th>
                <th class="text-center" style="width: 70px;">Satuan</th>
                <th class="text-right" style="width: 110px;">Stok Saat Ini</th>
                <th class="text-right" style="width: 110px;">Stok Minimum</th>
                <th class="text-center" style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanBahan as $index => $item)
            @php
                $displayUnit = \App\Helpers\UnitHelper::getDisplayUnit($item['satuan'], $item['stok_saat_ini']);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono">{{ $item['id_bahan_baku'] ?? '-' }}</td>
                <td><strong>{{ $item['nama_bahan'] }}</strong></td>
                <td class="text-center">{{ $displayUnit }}</td>
                <td class="text-right font-bold">{{ \App\Helpers\UnitHelper::formatQuantity($item['stok_saat_ini'], $item['satuan']) }}</td>
                <td class="text-right">{{ \App\Helpers\UnitHelper::formatQuantity($item['stok_minimum'], $item['satuan']) }}</td>
                <td class="text-center">{{ $item['status'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Data persediaan belum tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
