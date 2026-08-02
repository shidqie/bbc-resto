<?php

namespace App\Support;

final class WhatsAppNumber
{
    /**
     * Normalisasi nomor WA ke format lokal 08xxxxxxxxxx (10-14 digit).
     * Terima masukan: 081234567890, 81234567890, +6281234567890, 6281234567890.
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

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    /**
     * Cek apakah nomor adalah nomor WA Indonesia yang valid (08xxxxxxxxxx, 10-14 digit).
     */
    public static function valid(?string $number): bool
    {
        $digits = self::normalize($number);

        return $digits !== null && preg_match('/^08\d{8,12}$/', $digits) === 1;
    }
}
