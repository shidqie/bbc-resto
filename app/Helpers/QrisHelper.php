<?php

namespace App\Helpers;

class QrisHelper
{
    /**
     * Mengubah QRIS Statis menjadi Dinamis dengan menambahkan nominal transaksi.
     *
     * @param string $staticQris String QRIS asli
     * @param float|int $amount Nominal transaksi
     * @return string String QRIS dinamis
     */
    public static function generateDynamicQris(string $staticQris, $amount): string
    {
        // 1. Ubah tipe QRIS dari Statis (010211) menjadi Dinamis (010212)
        $qris = str_replace("010211", "010212", $staticQris);

        // 2. Buang Tag 63 (CRC) yang lama beserta isinya (8 karakter terakhir)
        if (substr($qris, -8, 4) === "6304") {
            $qris = substr($qris, 0, -8);
        } else {
            $pos = strrpos($qris, "6304");
            if ($pos !== false) {
                $qris = substr($qris, 0, $pos);
            }
        }

        // 3. Tambahkan Tag 54 (Transaction Amount)
        $amountStr = (string)intval($amount);
        $len = strlen($amountStr);
        $lenStr = str_pad($len, 2, "0", STR_PAD_LEFT);
        $tag54 = "54" . $lenStr . $amountStr;
        $qris .= $tag54;
        
        // 4. Tambahkan header Tag 63 (CRC) kembali sebelum kalkulasi
        $qris .= "6304";

        // 5. Hitung CRC16-CCITT
        $crc = self::calculateCRC16($qris);

        // Gabungkan string dengan CRC final
        return $qris . $crc;
    }

    private static function calculateCRC16(string $str): string
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($str); $i++) {
            $x = (($crc >> 8) ^ ord($str[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return strtoupper(str_pad(dechex($crc), 4, "0", STR_PAD_LEFT));
    }
}
