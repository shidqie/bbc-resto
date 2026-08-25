<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IdCodeGenerator
{
    /**
     * Get Base URL for QR Scanning:
     * - Production / Hosting: Uses domain URL (e.g. https://saungbabakancinta.com)
     * - Local Development: Uses LAN Wi-Fi IP (e.g. http://192.168.100.117:8000)
     */
    public static function getLanBaseUrl(): string
    {
        $appUrl = config('app.url');
        $appEnv = config('app.env');

        // 1. Jika production atau domain publik (bukan localhost/127.0.0.1), gunakan domain publik
        if ($appEnv === 'production' && !empty($appUrl) && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            return rtrim($appUrl, '/');
        }

        // 2. Jika request saat ini sudah menggunakan IP LAN (misal admin buka dari http://192.168.x.x:8000), gunakan host tersebut
        if (request() && request()->getHost() && !in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1', '0.0.0.0'])) {
            $scheme = request()->getScheme() ?: 'http';
            $port = request()->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : "";
            return "{$scheme}://" . request()->getHost() . $portSuffix;
        }

        // 3. Jika APP_URL di .env sudah diset ke IP LAN (192.168.x.x, 10.x.x.x, 172.x.x.x), gunakan APP_URL
        if (!empty($appUrl) && preg_match('/https?:\/\/([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)(:[0-9]+)?/', $appUrl, $m)) {
            if (!in_array($m[1], ['127.0.0.1', '0.0.0.0'])) {
                return rtrim($appUrl, '/');
            }
        }

        // 4. Deteksi otomatis IP Wi-Fi / Ethernet aktif pada OS (Mac / Linux)
        try {
            $output = @shell_exec("ifconfig 2>&1 | grep 'inet ' | grep -v '127.0.0.1' | awk '{print $2}'");
            if ($output) {
                $ips = array_filter(array_map('trim', explode("\n", $output)));
                foreach ($ips as $ip) {
                    if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $ip)) {
                        $port = (request() && request()->getPort()) ? request()->getPort() : 8000;
                        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : ":8000";
                        return "http://{$ip}{$portSuffix}";
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 5. Fallback hostname IP
        try {
            $host = gethostname();
            $ip = gethostbyname($host);
            if (filter_var($ip, FILTER_VALIDATE_IP) && !in_array($ip, ['127.0.0.1', '::1', '127.0.1.1'])) {
                $port = (request() && request()->getPort()) ? request()->getPort() : 8000;
                $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : ":8000";
                return "http://{$ip}{$portSuffix}";
            }
        } catch (\Throwable $e) {}

        return 'http://192.168.100.117:8000';
    }

    /**
     * 1. ID Pelanggan (Format: PLG000)
     */
    public static function generatePelangganId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('pelanggan')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'PLG' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 2. ID Jenis Pesanan (Format: AA -> DI, KT, NB)
     */
    public static function generateJenisPesananId($jenis): string
    {
        $jenisClean = strtolower(trim((string)$jenis));
        if (in_array($jenisClean, ['dine_in', 'dine in', 'dinein', '1', 1])) {
            return 'DI';
        }
        if (in_array($jenisClean, ['catering', 'katering', '2', 2])) {
            return 'KT';
        }
        if (in_array($jenisClean, ['nasi_box', 'nasi box', 'nasibox', '3', 3])) {
            return 'NB';
        }
        return strtoupper(substr($jenisClean, 0, 2));
    }

    /**
     * 3. ID Meja (Format: MJA00)
     */
    public static function generateMejaId($number): string
    {
        $num = (int) preg_replace('/[^0-9]/', '', (string)$number);
        return 'MJA' . str_pad($num > 0 ? $num : 1, 2, '0', STR_PAD_LEFT);
    }

    /**
     * 4. ID Pesanan (Format: PSN-YYYYMMDD-HHMMSS)
     */
    public static function generatePesananId($date = null): string
    {
        $now = $date ? Carbon::parse($date) : Carbon::now();
        $base = 'PSN-' . $now->format('Ymd-His');
        $code = $base;
        $counter = 1;
        while (DB::table('pesanan')->where('id_pesanan', $code)->exists()) {
            $code = $base . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }
        return $code;
    }

    /**
     * 5. ID Kategori Menu (Format: KTM00)
     */
    public static function generateKategoriMenuId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('kategori_menu')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'KTM' . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * 6. ID Menu (Format: MNU000)
     */
    public static function generateMenuId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('menu')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'MNU' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 7. ID Paket (Format: PKT-KT-00 / PKT-NB-00)
     */
    public static function generatePaketId($type, $sequence = null): string
    {
        $typeCode = (strtolower($type) === 'catering' || strtolower($type) === 'katering' || $type == 2) ? 'KT' : 'NB';
        if ($sequence === null) {
            $latest = DB::table('menu')->whereIn('jenis_menu_id', [2, 3])->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'PKT-' . $typeCode . '-' . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * 8. ID Detail Paket (Format: DPK000000)
     */
    public static function generateDetailPaketId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('komponen_paket')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'DPK' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * 9. ID Detail Pesanan (Format: DPS000000)
     */
    public static function generateDetailPesananId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('detail_pesanan')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'DPS' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * 10. ID Pembayaran (Format: BYR-YYYYMMDD-HHMMSS)
     */
    public static function generatePembayaranId($date = null): string
    {
        $now = $date ? Carbon::parse($date) : Carbon::now();
        return 'BYR-' . $now->format('Ymd-His');
    }

    /**
     * 11. ID Pengiriman (Format: KRM-YYYYMMDD-HHMMSS)
     */
    public static function generatePengirimanId($date = null): string
    {
        $now = $date ? Carbon::parse($date) : Carbon::now();
        return 'KRM-' . $now->format('Ymd-His');
    }

    /**
     * 12. ID Satuan (Format: STN00)
     */
    public static function generateSatuanId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('satuan')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'STN' . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * 13. ID Bahan Baku (Format: BHB000)
     */
    public static function generateBahanBakuId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('bahan_baku')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'BHB' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 14. ID Resep (Format: RSP000000)
     */
    public static function generateResepId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('resep_menu')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'RSP' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * 15. ID Stok (Format: STK000)
     */
    public static function generateStokId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('stok_bahan')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'STK' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 16. ID Kartu Stok / Mutasi (Format: KST-YYYYMMDD-HHMMSS)
     */
    public static function generateKartuStokId($date = null): string
    {
        $now = $date ? Carbon::parse($date) : Carbon::now();
        return 'KST-' . $now->format('Ymd-His');
    }

    /**
     * 17. ID Supplier / Pemasok (Format: SPL000)
     */
    public static function generateSupplierId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('pemasok')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'SPL' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 18. ID Pengadaan / PO (Format: PGD-YYYYMMDD-HHMMSS)
     */
    public static function generatePengadaanId($date = null): string
    {
        $now = $date ? Carbon::parse($date) : Carbon::now();
        return 'PGD-' . $now->format('Ymd-His');
    }

    /**
     * 19. ID Detail Pengadaan (Format: DPG000000)
     */
    public static function generateDetailPengadaanId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('detail_purchase_order')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'DPG' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * 20. ID Pembelian / Penerimaan (Format: PBL-YYYYMMDD-HHMMSS)
     */
    public static function generatePembelianId($date = null): string
    {
        $now = $date ? Carbon::parse($date) : Carbon::now();
        return 'PBL-' . $now->format('Ymd-His');
    }

    /**
     * 21. ID Detail Pembelian (Format: DPB000000)
     */
    public static function generateDetailPembelianId($sequence = null): string
    {
        if ($sequence === null) {
            $latest = DB::table('detail_penerimaan_bahan')->max('id') ?? 0;
            $sequence = $latest + 1;
        }
        return 'DPB' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }
}
