<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dokumen Cetak - RM Saung Babakan Cinta')</title>
    <style>
        @page {
            size: A4 @yield('orientation', 'portrait');
            margin: 25mm 20mm 20mm 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #111827;
            background: #ffffff;
            line-height: 1.4;
        }

        /* Header Centered Layout (Like Screenshot) */
        .doc-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 3px;
        }

        .doc-subtitle {
            font-size: 9.5pt;
            color: #4b5563;
        }

        .header-divider {
            border-bottom: 2.5px solid #111827;
            margin-top: 12px;
            margin-bottom: 22px;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9.5pt;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #374151;
            width: 130px;
        }

        .info-colon {
            width: 15px;
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
            margin-top: 10px;
            margin-bottom: 16px;
            font-size: 9pt;
        }

        table.pdf-table th, 
        table.pdf-table td {
            border: 1px solid #d1d5db;
            padding: 7px 9px;
        }

        table.pdf-table thead th {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: bold;
            font-size: 9pt;
            text-align: left;
        }

        table.pdf-table tr {
            page-break-inside: avoid;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold !important; }
        .font-mono { font-family: 'Courier', monospace !important; }

        /* Footer Counter */
        footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            height: 20px;
            font-size: 8pt;
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
