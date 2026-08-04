<?php

namespace App\Support;

final class WhatsAppNumber
{
    /**
     * Normalisasi nomor WA ke format internasional 628xxxxxxxxxx (11-14 digit).
     * Terima masukan: 081234567890, 81234567890, +6281234567890, 6281234567890.
     * Output: 6281234567890 (tanpa +, tanpa 0 di depan)
     */
    public static function normalize(?string $number): ?string
    {
        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }

    /**
     * Cek apakah nomor adalah nomor WA Indonesia yang valid (628xxxxxxxxxx, 11-14 digit).
     */
    public static function valid(?string $number): bool
    {
        $digits = self::normalize($number);

        return $digits !== null && preg_match('/^628\d{8,12}$/', $digits) === 1;
    }

    /**
     * Format nomor untuk display (08xx-xxxx-xxxx).
     * Input: 6281234567890 (format internal)
     * Output: 0812-3456-7890
     */
    public static function formatForDisplay(?string $number): string
    {
        $normalized = self::normalize($number);

        if (!$normalized) {
            return $number ?? '-';
        }

        // Convert back to 08xx format for display
        $local = '0' . substr($normalized, 2);

        // Format: 08xx-xxxx-xxxx (or 08xx-xxx-xxxx for shorter numbers)
        if (strlen($local) >= 12) {
            return substr($local, 0, 4) . '-' . substr($local, 4, 4) . '-' . substr($local, 8);
        } elseif (strlen($local) >= 10) {
            return substr($local, 0, 4) . '-' . substr($local, 4, 3) . '-' . substr($local, 7);
        }

        return $local;
    }
}
