<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dokumen Cetak - RM Saung Babakan Cinta')</title>
    <style>
        @font-face {
            font-family: 'Outfit';
            src: url('{{ public_path("fonts/Outfit-Regular.ttf") }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Outfit';
            src: url('{{ public_path("fonts/Outfit-Medium.ttf") }}') format('truetype');
            font-weight: 500;
            font-style: normal;
        }
        @font-face {
            font-family: 'Outfit';
            src: url('{{ public_path("fonts/Outfit-SemiBold.ttf") }}') format('truetype');
            font-weight: 600;
            font-style: normal;
        }
        @font-face {
            font-family: 'Outfit';
            src: url('{{ public_path("fonts/Outfit-Bold.ttf") }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @page {
            size: A4 @yield('orientation', 'portrait');
            margin: 15mm 14mm 15mm 14mm;
        }

        * {
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif !important;
        }

        body {
            font-family: 'Outfit', sans-serif !important;
            font-size: 9pt;
            color: #111827;
            background: #ffffff;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* ─── HEADER KOP RESTORAN ─── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .brand-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0D3024;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
            line-height: 1.2;
        }
        .brand-address {
            font-size: 7.8pt;
            color: #4b5563;
            line-height: 1.35;
        }
        .divider-line {
            border-bottom: 2.5px solid #0D3024;
            margin-top: 8px;
            margin-bottom: 14px;
        }

        /* ─── JUDUL DOKUMEN ─── */
        .doc-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .doc-title {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 2px;
        }

        .doc-subtitle {
            font-size: 8.5pt;
            color: #4b5563;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9pt;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #374151;
            width: 120px;
        }

        .info-colon {
            width: 12px;
            text-align: center;
        }

        .info-val {
            font-weight: normal;
            color: #111827;
        }

        /* Table Design Matching Screenshot Exactly */
        table.pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
            font-size: 8.5pt;
            table-layout: fixed;
            word-wrap: break-word;
        }

        table.pdf-table th, 
        table.pdf-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            word-wrap: break-word;
            overflow: hidden;
        }

        table.pdf-table thead th {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: bold;
            font-size: 8.5pt;
            text-align: left;
        }

        table.pdf-table tr {
            page-break-inside: avoid;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold !important; }
        .font-mono { font-family: 'Outfit', sans-serif !important; }

        /* Footer Counter */
        footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            height: 15px;
            font-size: 7.5pt;
            color: #6b7280;
        }

        footer table {
            width: 100%;
            border-collapse: collapse;
        }

        footer .page-number:after {
            content: counter(page) " dari " counter(pages);
        }

        .notes-text {
            margin-top: 12px;
            font-size: 8.5pt;
            color: #4b5563;
            line-height: 1.4;
        }
    </style>
    @yield('styles')
</head>
<body>
    @php
        $logoPath = public_path('images/logo-saung.png');
        $logoBase64 = file_exists($logoPath) ? ('data:image/png;base64,' . base64_encode(file_get_contents($logoPath))) : null;
    @endphp

    {{-- ── HEADER KOP RESTORAN ── --}}
    <table class="header-table">
        <tr>
            @if($logoBase64)
            <td style="width: 52px; padding-right: 12px; vertical-align: middle;">
                <img src="{{ $logoBase64 }}" style="width: 48px; height: auto;" alt="Logo Saung Babakan Cinta" />
            </td>
            @endif
            <td style="vertical-align: middle;">
                <div class="brand-title">RUMAH MAKAN SAUNG BABAKAN CINTA</div>
                <div class="brand-address">
                    Jl. Ciloa No. KM 6, Pasirhalang, Kec. Cisarua, Kabupaten Bandung Barat, Jawa Barat 40551
                </div>
            </td>
        </tr>
    </table>

    <div class="divider-line"></div>

    <!-- Main Body Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer Page Counter -->
    <footer>
        <table>
            <tr>
                <td class="text-left" style="width: 70%;">
                    Dicetak dari Sistem Informasi RM Saung Babakan Cinta &bull; {{ \Carbon\Carbon::now()->translatedFormat('d/m/Y H:i') }} WIB
                </td>
                <td class="text-right page-number" style="width: 30%;">
                    Halaman 
                </td>
            </tr>
        </table>
    </footer>

</body>
</html>
