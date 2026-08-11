<?php

if (!function_exists('format_stok')) {
    /**
     * Format jumlah stok menjadi tampilan yang mudah dibaca berdasarkan satuan.
     *
     * Logika konversi:
     * - gram (g)      : jika >= 1000 → tampilkan dalam kg, jika < 1000 → tetap g
     * - ml            : jika >= 1000 → tampilkan dalam L, jika < 1000 → tetap ml
     * - satuan lainnya: tampilkan apa adanya tanpa desimal trailing zero
     *
     * @param  float|string  $jumlah
     * @param  string|null   $singkatan  Singkatan satuan (g, kg, ml, l, botol, dsb)
     * @return string  Contoh: "2,5 kg", "500 g", "1,5 L", "10 Botol"
     */
    function format_stok($jumlah, ?string $singkatan): string
    {
        $jumlah = (float) $jumlah;
        $singkatan = strtolower(trim($singkatan ?? ''));

        if ($singkatan === 'g' || $singkatan === 'gram') {
            if ($jumlah >= 1000) {
                $kg = $jumlah / 1000;
                return rtrim(rtrim(number_format($kg, 3, ',', '.'), '0'), ',') . ' kg';
            }
            return number_format($jumlah, 0, ',', '.') . ' g';
        }

        if ($singkatan === 'ml' || $singkatan === 'mililiter') {
            if ($jumlah >= 1000) {
                $liter = $jumlah / 1000;
                return rtrim(rtrim(number_format($liter, 3, ',', '.'), '0'), ',') . ' L';
            }
            return number_format($jumlah, 0, ',', '.') . ' ml';
        }

        // Satuan berbasis decimal (kg, l/liter, sdm, sdt, dll) — bersihkan trailing zero
        $decimals = ($jumlah != floor($jumlah)) ? 3 : 0;
        $angka = rtrim(rtrim(number_format($jumlah, $decimals, ',', '.'), '0'), ',');

        // Tampilkan singkatan
        $label = match ($singkatan) {
            'kg'     => 'kg',
            'l', 'liter' => 'L',
            'botol'  => 'Botol',
            'buah'   => 'Buah',
            'pcs'    => 'pcs',
            'bks', 'bungkus' => 'Bungkus',
            'ikat', 'ikt' => 'Ikat',
            'sdm'    => 'sdm',
            'sdt'    => 'sdt',
            'sct', 'sachet' => 'Sachet',
            'kardus' => 'Kardus',
            'lbr', 'lembar' => 'Lembar',
            'prsi', 'porsi' => 'Porsi',
            default  => $singkatan,
        };

        return $angka . ' ' . $label;
    }
}

if (!function_exists('status_stok_badge')) {
    /**
     * Hitung status stok dan return HTML badge.
     *
     * @param  float  $stokSaatIni
     * @param  float  $stokMinimal
     * @return string HTML badge
     */
    function status_stok_badge(float $stokSaatIni, float $stokMinimal): string
    {
        if ($stokSaatIni <= 0) {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Habis
                    </span>';
        }

        if ($stokSaatIni <= $stokMinimal) {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>Menipis
                    </span>';
        }

        return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>Aman
                </span>';
    }
}
