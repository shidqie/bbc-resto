@extends('pdf.layout')

@section('title', 'Laporan Persediaan Bahan Baku - RM Saung Babakan Cinta')

@section('content')
    <div class="doc-header">
        <div class="doc-title">{{ $judulLaporan ?? 'LAPORAN PERSEDIAAN BAHAN BAKU' }}</div>
        <div class="doc-subtitle">
            @if(($tab ?? 'harian') === 'katering')
                Kategori: Stok Katering &bull; Per Tanggal: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            @else
                Kategori: Stok Harian (Dine-In & Nasi Box) &bull; Per Tanggal: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            @endif
        </div>
    </div>

    <table class="pdf-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: {{ ($tab ?? 'harian') === 'katering' ? '18%' : '16%' }};">Kode</th>
                <th style="width: {{ ($tab ?? 'harian') === 'katering' ? '37%' : '30%' }};">Nama Bahan Baku</th>
                <th class="text-center" style="width: {{ ($tab ?? 'harian') === 'katering' ? '10%' : '9%' }};">Satuan</th>
                @if(($tab ?? 'harian') === 'katering')
                    <th class="text-right" style="width: 18%;">Stok Sisa Katering</th>
                    <th class="text-center" style="width: 12%;">Status</th>
                @else
                    <th class="text-right" style="width: 14%;">Stok Saat Ini</th>
                    <th class="text-right" style="width: 14%;">Stok Minimum</th>
                    <th class="text-center" style="width: 12%;">Status</th>
                @endif
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
                @if(($tab ?? 'harian') === 'katering')
                    <td class="text-right font-bold">{{ \App\Helpers\UnitHelper::formatQuantity($item['stok_saat_ini'], $item['satuan']) }}</td>
                    <td class="text-center">{{ $item['status'] }}</td>
                @else
                    <td class="text-right font-bold">{{ \App\Helpers\UnitHelper::formatQuantity($item['stok_saat_ini'], $item['satuan']) }}</td>
                    <td class="text-right">{{ \App\Helpers\UnitHelper::formatQuantity($item['stok_minimum'], $item['satuan']) }}</td>
                    <td class="text-center">{{ $item['status'] }}</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ ($tab ?? 'harian') === 'katering' ? 6 : 7 }}" class="text-center">Data persediaan belum tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
