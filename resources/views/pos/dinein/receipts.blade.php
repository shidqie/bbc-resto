<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Dine-In #{{ $pesanan->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inconsolata:wght@400;700&display=swap');
        
        body {
            font-family: 'Inconsolata', 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 20px;
            background: #e5e7eb;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        * { box-sizing: border-box; }

        .receipt-card {
            background: #fff;
            padding: 16px;
            width: 260px; /* Cocok untuk thermal 58mm/80mm */
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border-radius: 4px;
            border-top: 4px solid #111827;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-lg { font-size: 15px; }
        .text-xl { font-size: 22px; }
        .text-2xl { font-size: 26px; }
        
        .divider {
            border-top: 1.5px dotted #111827;
            margin: 12px 0;
        }
        .divider-solid {
            border-top: 1.5px solid #111827;
            margin: 12px 0;
        }
        
        .flex { display: flex; justify-content: space-between; }
        .flex-col { display: flex; flex-direction: column; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        
        /* Table-like structure for items */
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }
        .item-name { flex: 1; padding-right: 10px; }
        .item-price { text-align: right; white-space: nowrap; }
        .item-qty { min-width: 25px; font-weight: bold; }

        .catatan-box {
            background: #f3f4f6;
            border-left: 2px solid #374151;
            padding: 4px 6px;
            margin-top: 4px;
            font-size: 11px;
            font-style: italic;
        }

        .kot-item {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
        }
        .kot-item:last-child { border-bottom: none; }

        .action-bar {
            width: 100%;
            max-width: 820px;
            margin: 0 auto 20px auto;
        }
        .btn-print {
            display: block;
            width: 100%;
            padding: 16px;
            background: #2563EB;
            color: white;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 12px;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
        }
        .btn-print:hover { background: #1D4ED8; }
        
        @media print {
            body {
                background: #fff;
                padding: 0;
                display: block;
            }
            .receipt-card {
                box-shadow: none;
                border-top: none;
                border-radius: 0;
                page-break-after: always;
                width: 100%;
                max-width: 100%;
                margin: 0 auto;
                padding: 0;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    @if(request('embedded') != 'true')
        <div class="action-bar no-print">
            <button class="btn-print" onclick="window.print()">🖨️ Cetak Semua Struk</button>
            <div style="text-align: center;">
                <a href="{{ route('pos.dinein.index') }}" style="color: #6B7280; font-family: sans-serif; font-weight: bold; text-decoration: none;">&larr; KEMBALI KE POS</a>
            </div>
        </div>
    @endif

    <!-- ==========================================
         1. STRUK KONSUMEN (PEMBAYARAN)
         ========================================== -->
    <div class="receipt-card">
        <div class="text-center mb-4">
            <h2 class="text-xl font-bold" style="margin:0; letter-spacing: 1px;">SAUNG BABAKAN CINTA</h2>
            <p style="margin:4px 0 0 0; font-size: 11px;">Jl. Raya Babakan Cinta No. 1</p>
            <p style="margin:0; font-size: 11px;">Telp: 0812-3456-7890</p>
        </div>
        
        <div class="divider-solid"></div>
        
        <div class="flex mb-1">
            <span>No: <b>#{{ $pesanan->id }}</b></span>
            <span>{{ date('d/m/Y H:i') }}</span>
        </div>
        <div class="flex mb-1">
            <span>Meja: <b>{{ $pesanan->meja->nomor_meja }}</b></span>
            <span>Kasir: {{ explode(' ', auth()->user()->name)[0] }}</span>
        </div>
        
        <div class="divider"></div>
        
        <!-- Items -->
        @foreach($pesanan->items as $item)
        <div class="mb-2">
            <div class="item-row font-bold">
                <span class="item-name">{{ $item->menu->nama }}</span>
            </div>
            <div class="item-row">
                <span class="item-qty">{{ $item->qty }}x</span>
                <span class="item-name">{{ number_format($item->menu->harga, 0, ',', '.') }}</span>
                <span class="item-price">{{ number_format($item->qty * $item->menu->harga, 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
        
        <div class="divider-solid"></div>
        
        <!-- Totals -->
        <div class="flex font-bold text-lg mb-1">
            <span>TOTAL</span>
            <span>Rp {{ number_format($pesanan->pembayaran->total, 0, ',', '.') }}</span>
        </div>
        <div class="flex mb-1">
            <span>METODE BAYAR</span>
            <span>{{ strtoupper($pesanan->pembayaran->metode_bayar) }}</span>
        </div>
        
        <div class="divider"></div>
        
        <div class="text-center" style="font-size: 11px; margin-top: 15px;">
            <p class="font-bold mb-1" style="margin-top:0;">TERIMA KASIH</p>
            <p style="margin:0;">Atas Kunjungan Anda</p>
            <p style="margin:4px 0 0 0;">IG: @saungbabakancinta</p>
        </div>
    </div>

    <!-- ==========================================
         2. STRUK CHECKER (UNTUK DI MEJA)
         ========================================== -->
    <div class="receipt-card">
        <div class="text-center mb-4">
            <h2 style="margin:0; font-size: 18px;">CHECKER MEJA</h2>
            <div class="font-bold text-2xl" style="border: 2px solid #000; padding: 6px; margin-top: 10px; border-radius: 4px;">
                {{ strtoupper($pesanan->meja->nomor_meja) }}
            </div>
        </div>
        
        <div class="flex mb-1"><span>Order:</span> <span class="font-bold">#{{ $pesanan->id }}</span></div>
        <div class="flex mb-1"><span>Waktu:</span> <span>{{ date('d/m/Y H:i') }}</span></div>
        
        <div class="divider"></div>
        <div class="font-bold mb-2">DAFTAR PESANAN:</div>
        
        @foreach($pesanan->items as $item)
        <div class="mb-2">
            <div class="flex">
                <span class="font-bold" style="width: 30px;">{{ $item->qty }}x</span>
                <span style="flex: 1;">{{ $item->menu->nama }}</span>
                <span style="border: 1px solid #000; width: 15px; height: 15px; display: inline-block; border-radius: 2px;"></span>
            </div>
            @if($item->catatan)
                <div class="catatan-box" style="margin-left: 30px;">{{ $item->catatan }}</div>
            @endif
        </div>
        @endforeach
        
        <div class="divider"></div>
        
        <div class="text-center font-bold" style="font-size: 11px; margin-top: 15px;">
            SILAKAN TINGGALKAN STRUK INI<br>DI MEJA UNTUK PENCOCOKAN
        </div>
    </div>

    <!-- ==========================================
         3. STRUK K.O.T (UNTUK DAPUR/BAR)
         ========================================== -->
    <div class="receipt-card">
        <div class="text-center mb-4">
            <h2 style="margin:0; font-size: 18px;">K.O.T DAPUR</h2>
            <div class="font-bold text-2xl" style="border: 3px dashed #000; padding: 6px; margin-top: 10px; border-radius: 4px;">
                {{ strtoupper($pesanan->meja->nomor_meja) }}
            </div>
        </div>
        
        <div class="flex font-bold text-lg mb-2">
            <span>JAM: {{ date('H:i') }}</span>
            <span>#{{ $pesanan->id }}</span>
        </div>
        
        <div class="divider-solid" style="border-top-width: 2px;"></div>
        
        @foreach($pesanan->items as $item)
        <div class="kot-item">
            <div class="flex" style="align-items: flex-start;">
                <span class="font-bold text-xl" style="width: 40px;">{{ $item->qty }}x</span>
                <span class="font-bold text-lg" style="flex: 1; line-height: 1.2;">{{ $item->menu->nama }}</span>
            </div>
            @if($item->catatan)
                <div class="catatan-box font-bold text-lg" style="margin-left: 40px; margin-top: 6px; background: #000; color: #fff; border-left: none;">
                    * {{ $item->catatan }}
                </div>
            @endif
        </div>
        @endforeach
        
        <div class="divider-solid" style="border-top-width: 2px;"></div>
        
        <div class="text-center font-bold text-lg" style="margin-top: 10px;">
            HARAP SEGERA DIPROSES!
        </div>
    </div>

</body>
</html>
