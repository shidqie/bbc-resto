<?php

namespace App\Helpers;

class UnitHelper
{
    /**
     * Format base quantity (gram, ml, pcs) for user display.
     */
    public static function formatQuantity($jumlah, $satuan = 'gram'): string
    {
        $jumlah = (float) $jumlah;
        $satuanClean = strtolower(trim((string) $satuan));

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
        $unitDisplay = !empty($satuan) ? $satuan : 'pcs';
        return "{$formatted} {$unitDisplay}";
    }

    /**
     * Get display unit string (kg, liter, pcs, gram, ml).
     */
    public static function getDisplayUnit($satuan, $jumlah = 0): string
    {
        $jumlah = (float) $jumlah;
        $satuanClean = strtolower(trim((string) $satuan));

        if (in_array($satuanClean, ['gram', 'g', 'gr', 'kilogram', 'kg'])) {
            return $jumlah >= 1000 ? 'kg' : 'gram';
        }

        if (in_array($satuanClean, ['ml', 'mililiter', 'liter', 'l'])) {
            return $jumlah >= 1000 ? 'liter' : 'ml';
        }

        return !empty($satuan) ? $satuan : 'pcs';
    }

    /**
     * Convert friendly input (e.g. 50 kg or 20 liter) into base unit quantity (gram or ml).
     */
    public static function toBaseQuantity($jumlah, $satuan = 'gram'): float
    {
        $jumlah = (float) $jumlah;
        $satuanClean = strtolower(trim((string) $satuan));

        if (in_array($satuanClean, ['kg', 'kilogram'])) {
            return $jumlah * 1000;
        }

        if (in_array($satuanClean, ['liter', 'l'])) {
            return $jumlah * 1000;
        }

        return $jumlah;
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
