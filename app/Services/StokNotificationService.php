<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\NotifikasiStok;
use App\Models\StokBahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StokNotificationService
{
    /**
     * Cek dan buat notifikasi stok menipis/habis per jenis persediaan.
     * Stok Harian dan Catering diperiksa terpisah karena batas minimum keduanya berbeda.
     */
    public function checkAndNotify()
    {
        $notificationsCreated = 0;

        $rows = StokBahan::with(['bahan_baku.satuan'])
            ->whereColumn('jumlah_stok', '<=', 'stok_minimal')
            ->where('stok_minimal', '>', 0)
            ->get();

        foreach ($rows as $stokRow) {
            $bahan = $stokRow->bahan_baku;
            if (! $bahan || ! $bahan->status_aktif) {
                continue;
            }

            $stok = (float) $stokRow->jumlah_stok;
            $min = (float) $stokRow->stok_minimal;
            $jenis = $stok <= 0 ? 'habis' : 'menipis';
            $jenisNama = $stokRow->jenis_persediaan === 'catering' ? 'Catering' : 'Harian';

            // Check if notification already exists and unread
            $existing = NotifikasiStok::where('bahan_baku_id', $bahan->id)
                ->where('jenis_persediaan', $stokRow->jenis_persediaan)
                ->where('jenis', $jenis)
                ->where('dibaca', false)
                ->first();

            if (! $existing) {
                $satuanSingkatan = $bahan->satuan->singkatan ?? '';
                $pesan = $stok <= 0
                    ? "Stok {$jenisNama} {$bahan->nama_bahan} ({$bahan->kode_bahan}) HABIS! Stok saat ini: {$stok} {$satuanSingkatan}. Segera lakukan pengadaan."
                    : "Stok {$jenisNama} {$bahan->nama_bahan} ({$bahan->kode_bahan}) MENIPIS. Stok saat ini: {$stok} {$satuanSingkatan} (Minimal: {$min} {$satuanSingkatan}).";

                NotifikasiStok::create([
                    'bahan_baku_id' => $bahan->id,
                    'jenis_persediaan' => $stokRow->jenis_persediaan,
                    'jenis' => $jenis,
                    'stok_saat_ini' => $stok,
                    'stok_minimal' => $min,
                    'pesan' => $pesan,
                    'dibaca' => false,
                ]);

                $notificationsCreated++;
            }
        }

        return $notificationsCreated;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id, $userId)
    {
        return NotifikasiStok::where('id', $id)->update([
            'dibaca' => true,
            'dibaca_pada' => now(),
            'dibaca_oleh' => $userId,
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead($userId)
    {
        return NotifikasiStok::where('dibaca', false)->update([
            'dibaca' => true,
            'dibaca_pada' => now(),
            'dibaca_oleh' => $userId,
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        return NotifikasiStok::where('dibaca', false)->count();
    }

    /**
     * Get unread notifications with bahan baku info
     */
    public function getUnreadNotifications($limit = 10)
    {
        return NotifikasiStok::with('bahan_baku.satuan')
            ->where('dibaca', false)
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get all notifications (paginated)
     */
    public function getAllNotifications($perPage = 15)
    {
        return NotifikasiStok::with('bahan_baku.satuan', 'dibacaOleh')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create notification for stock adjustment
     */
    public function notifyPenyesuaian($bahanBakuId, $stokSistem, $stokFisik, $alasan, $jenisPersediaan = 'harian')
    {
        $bahan = BahanBaku::with('satuan')->find($bahanBakuId);
        if (! $bahan) {
            return;
        }

        $selisih = $stokFisik - $stokSistem;
        $jenis = $selisih > 0 ? 'penyesuaian' : 'penyesuaian';

        $satuanSingkatan = $bahan->satuan->singkatan ?? '';
        $jenisNama = $jenisPersediaan === 'catering' ? 'Catering' : 'Harian';
        $pesan = "Penyesuaian stok {$jenisNama} {$bahan->nama_bahan}: {$stokSistem} → {$stokFisik} {$satuanSingkatan} ({$alasan}).";

        return NotifikasiStok::create([
            'bahan_baku_id' => $bahanBakuId,
            'jenis_persediaan' => $jenisPersediaan,
            'jenis' => $jenis,
            'stok_saat_ini' => $stokFisik,
            'stok_minimal' => $bahan->stok_minimal,
            'pesan' => $pesan,
            'dibaca' => false,
        ]);
    }

    /**
     * Create notification for stock received (penerimaan)
     */
    public function notifyPenerimaan($bahanBakuId, $jumlahDiterima, $stokAwal, $jenisPersediaan = 'harian')
    {
        $bahan = BahanBaku::with('satuan')->find($bahanBakuId);
        if (! $bahan) {
            return;
        }

        $stokAkhir = $stokAwal + $jumlahDiterima;

        $satuanSingkatan = $bahan->satuan->singkatan ?? '';
        $jenisNama = $jenisPersediaan === 'catering' ? 'Catering' : 'Harian';
        $pesan = "Penerimaan bahan {$jenisNama} {$bahan->nama_bahan}: +{$jumlahDiterima} {$satuanSingkatan}. Stok: {$stokAwal} → {$stokAkhir} {$satuanSingkatan}.";

        return NotifikasiStok::create([
            'bahan_baku_id' => $bahanBakuId,
            'jenis_persediaan' => $jenisPersediaan,
            'jenis' => 'penerimaan',
            'stok_saat_ini' => $stokAkhir,
            'stok_minimal' => $bahan->stok_minimal,
            'pesan' => $pesan,
            'dibaca' => false,
        ]);
    }
}
