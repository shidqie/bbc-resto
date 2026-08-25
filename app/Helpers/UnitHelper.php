<?php

namespace App\Helpers;

class UnitHelper
{
    /**
     * Dapatkan nama satuan standar untuk Pengadaan / Pembelian (Kg, Liter, Pcs).
     */
    public static function getPurchasingUnit($satuan): string
    {
        $name = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string)$satuan));

        if (in_array($name, ['gram', 'g', 'gr', 'kilogram', 'kg'])) {
            return 'Kg';
        }
        if (in_array($name, ['ml', 'mililiter', 'liter', 'l'])) {
            return 'Liter';
        }
        if (in_array($name, ['pcs', 'buah', 'btr', 'butir', 'btg', 'batang', 'cup', 'bks', 'bungkus', 'lbr', 'lembar', 'sct', 'sachet', 'ikat', 'ikt', 'botol', 'prsi', 'porsi'])) {
            return !empty($name) ? (is_object($satuan) ? ($satuan->singkatan ?? $satuan->nama_satuan ?? 'Pcs') : $satuan) : 'Pcs';
        }
        return !empty($name) ? (is_object($satuan) ? ($satuan->singkatan ?? $satuan->nama_satuan ?? 'Pcs') : $satuan) : 'Pcs';
    }

    /**
     * Dapatkan ID satuan standar untuk Pengadaan (1 = Kilogram, 3 = Liter, 10 = Pcs).
     */
    public static function getPurchasingSatuanId($satuan): int
    {
        $name = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string)$satuan));

        if (in_array($name, ['gram', 'g', 'gr', 'kilogram', 'kg'])) {
            return 1; // Kilogram
        }
        if (in_array($name, ['ml', 'mililiter', 'liter', 'l'])) {
            return 3; // Liter
        }
        if (is_object($satuan) && !empty($satuan->id)) {
            return (int) $satuan->id;
        }
        return 10; // Pcs
    }

    /**
     * Dapatkan nama satuan standar untuk Persediaan & Resep (Gram, Ml, Pcs).
     */
    public static function getBaseUnit($satuan): string
    {
        $name = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string)$satuan));

        if (in_array($name, ['gram', 'g', 'gr', 'kilogram', 'kg'])) {
            return 'Gram';
        }
        if (in_array($name, ['ml', 'mililiter', 'liter', 'l'])) {
            return 'Ml';
        }
        return !empty($name) ? (is_object($satuan) ? ($satuan->singkatan ?? $satuan->nama_satuan ?? 'Pcs') : $satuan) : 'Pcs';
    }

    /**
     * Konversi kuantitas dari Base Unit (Gram, Ml, Pcs) ke Purchasing Unit (Kg, Liter, Pcs).
     */
    public static function toPurchasingQuantity($jumlah, $satuan = 'gram'): float
    {
        $jumlah = (float) $jumlah;
        $satuanClean = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string)$satuan));

        if (in_array($satuanClean, ['gram', 'g', 'gr'])) {
            return $jumlah / 1000;
        }
        if (in_array($satuanClean, ['ml', 'mililiter'])) {
            return $jumlah / 1000;
        }
        return $jumlah;
    }

    /**
     * Konversi kuantitas dari Purchasing Unit (Kg, Liter, Pcs) ke Base Unit (Gram, Ml, Pcs).
     */
    public static function toBaseQuantity($jumlah, $satuan = 'gram'): float
    {
        $jumlah = (float) $jumlah;
        $satuanClean = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string)$satuan));

        if (in_array($satuanClean, ['kg', 'kilogram'])) {
            return $jumlah * 1000;
        }
        if (in_array($satuanClean, ['liter', 'l'])) {
            return $jumlah * 1000;
        }
        return $jumlah;
    }

    /**
     * Konversi harga beli per Purchasing Unit (Kg, Liter, Pcs) ke harga per Base Unit (Gram, Ml, Pcs).
     */
    public static function toBasePrice($hargaBeli, $satuan = 'gram'): float
    {
        $hargaBeli = (float) $hargaBeli;
        $satuanClean = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string)$satuan));

        if (in_array($satuanClean, ['kg', 'kilogram', 'gram', 'g', 'gr'])) {
            return $hargaBeli > 0 ? $hargaBeli / 1000 : 0;
        }
        if (in_array($satuanClean, ['liter', 'l', 'ml', 'mililiter'])) {
            return $hargaBeli > 0 ? $hargaBeli / 1000 : 0;
        }
        return $hargaBeli;
    }

    /**
     * Konversi harga per Base Unit (Gram, Ml, Pcs) ke harga per Purchasing Unit (Kg, Liter, Pcs).
     */
    public static function toPurchasingPrice($hargaBase, $satuan = 'gram'): float
    {
        $hargaBase = (float) $hargaBase;
        $satuanClean = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string)$satuan));

        if (in_array($satuanClean, ['gram', 'g', 'gr', 'kg', 'kilogram'])) {
            return $hargaBase < 500 ? $hargaBase * 1000 : $hargaBase;
        }
        if (in_array($satuanClean, ['ml', 'mililiter', 'liter', 'l'])) {
            return $hargaBase < 500 ? $hargaBase * 1000 : $hargaBase;
        }
        return $hargaBase;
    }

    /**
     * Format base quantity (gram, ml, pcs) for user display.
     */
    public static function formatQuantity($jumlah, $satuan = 'gram'): string
    {
        $jumlah = (float) $jumlah;
        $satuanClean = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string) $satuan));

        // Weight / Gram
        if (in_array($satuanClean, ['gram', 'g', 'gr', 'kilogram', 'kg'])) {
            if ($jumlah >= 1000) {
                $kg = $jumlah / 1000;
                $formatted = self::formatNumber($kg);
                return "{$formatted} kg";
            }
            $formatted = self::formatNumber($jumlah);
            return "{$formatted} gram";
        }

        // Liquid / Ml
        if (in_array($satuanClean, ['ml', 'mililiter', 'liter', 'l'])) {
            if ($jumlah >= 1000) {
                $liter = $jumlah / 1000;
                $formatted = self::formatNumber($liter);
                return "{$formatted} liter";
            }
            $formatted = self::formatNumber($jumlah);
            return "{$formatted} ml";
        }

        // Piece / Units
        $formatted = self::formatNumber($jumlah);
        $unitDisplay = !empty($satuanClean) ? $satuanClean : 'pcs';
        return "{$formatted} {$unitDisplay}";
    }

    /**
     * Get display unit string (kg, liter, pcs, gram, ml).
     */
    public static function getDisplayUnit($satuan, $jumlah = 0): string
    {
        $jumlah = (float) $jumlah;
        $satuanClean = is_object($satuan) 
            ? (strtolower(trim($satuan->singkatan ?? $satuan->nama_satuan ?? '')))
            : strtolower(trim((string) $satuan));

        if (in_array($satuanClean, ['gram', 'g', 'gr', 'kilogram', 'kg'])) {
            return $jumlah >= 1000 ? 'kg' : 'gram';
        }

        if (in_array($satuanClean, ['ml', 'mililiter', 'liter', 'l'])) {
            return $jumlah >= 1000 ? 'liter' : 'ml';
        }

        return !empty($satuanClean) ? $satuanClean : 'pcs';
    }

    /**
     * Format number cleanly without unnecessary trailing decimals (e.g. 1.5 instead of 1.50, 25 instead of 25.00).
     */
    public static function formatNumber($number): string
    {
        $floatVal = (float) $number;
        if (floor($floatVal) == $floatVal) {
            return number_format($floatVal, 0, ',', '.');
        }
        return str_replace('.', ',', rtrim(rtrim(number_format($floatVal, 3, '.', ''), '0'), '.'));
    }
}
