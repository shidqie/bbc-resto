<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\DetailPengadaanBahan;
use App\Models\PengadaanBahan;
use App\Models\Pesanan;
use App\Models\StatusPengadaan;
use App\Models\StokCatering;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    /**
     * Hitung kebutuhan bahan baku untuk pesanan catering
     * berdasarkan resep menu dan jumlah porsi
     */
    public function calculateKebutuhanCatering($pesanan_id)
    {
        $pesanan = Pesanan::with('detail_pesanan.produk.resep_produk')->find($pesanan_id);
        if (! $pesanan) {
            return;
        }

        // Kumpulkan total kebutuhan per bahan baku
        $kebutuhanPerBahan = [];

        foreach ($pesanan->detail_pesanan as $detail) {
            $produk = $detail->produk;
            if ($produk && $produk->resep_produk) {
                foreach ($produk->resep_produk as $resep) {
                    $bahan_id = $resep->bahan_baku_id;
                    $jumlahKebutuhan = $resep->jumlah * $detail->jumlah;

                    if (! isset($kebutuhanPerBahan[$bahan_id])) {
                        $kebutuhanPerBahan[$bahan_id] = 0;
                    }
                    $kebutuhanPerBahan[$bahan_id] += $jumlahKebutuhan;
                }
            }
        }

        DB::transaction(function () use ($pesanan_id, $kebutuhanPerBahan) {
            $bahanKurang = [];

            foreach ($kebutuhanPerBahan as $bahan_id => $totalKebutuhan) {
                $stokCatering = StokCatering::firstOrNew([
                    'pesanan_id' => $pesanan_id,
                    'bahan_baku_id' => $bahan_id,
                ]);

                $stokCatering->kebutuhan = $totalKebutuhan;
                $stokCatering->save();

                // Hitung kekurangan (jika kebutuhan lebih besar dari yang sudah diterima)
                if ($stokCatering->diterima < $totalKebutuhan) {
                    $bahanKurang[] = [
                        'bahan_baku_id' => $bahan_id,
                        'jumlah_kurang' => $totalKebutuhan - $stokCatering->diterima,
                    ];
                }
            }

            // Jika ada bahan yang kurang, buat draft pengadaan
            if (count($bahanKurang) > 0) {
                $this->createDraftPengadaan($bahanKurang, 'CATERING', $pesanan_id);
            }
        });
    }

    /**
     * Cek apakah ada stok operasional yang mencapai batas minimum
     */
    public function checkStokMinimum()
    {
        // Ambil bahan baku yang stok fisiknya <= stok minimal
        $bahanMenipis = DB::table('stok_bahan_baku')
            ->join('bahan_baku', 'stok_bahan_baku.bahan_baku_id', '=', 'bahan_baku.id')
            ->whereColumn('stok_bahan_baku.jumlah_stok', '<=', 'bahan_baku.stok_minimal')
            ->where('bahan_baku.status_aktif', true)
            ->where('bahan_baku.stok_minimal', '>', 0)
            ->select('bahan_baku.id', 'bahan_baku.stok_minimal', 'stok_bahan_baku.jumlah_stok')
            ->get();

        if ($bahanMenipis->count() > 0) {
            $bahanKurang = [];
            foreach ($bahanMenipis as $item) {
                // Default usulan pesanan = stok minimal (atau sesuai kebijakan)
                $bahanKurang[] = [
                    'bahan_baku_id' => $item->id,
                    'jumlah_kurang' => $item->stok_minimal > 0 ? $item->stok_minimal : 10, // fallback
                ];
            }

            // Cek apakah sudah ada draft pengadaan operasional yang belum diproses untuk menghindari duplikasi
            $existingDraft = PengadaanBahan::where('jenis_pengadaan', 'OPERASIONAL')
                ->whereHas('status_pengadaan', function ($q) {
                    $q->where('kode_status', 'DRAFT'); // asumsi DRAFT
                })
                ->first();

            if (! $existingDraft) {
                $this->createDraftPengadaan($bahanKurang, 'OPERASIONAL', null);
            }
        }
    }

    /**
     * Internal method to create a draft PO
     */
    private function createDraftPengadaan($bahanList, $jenis, $pesanan_id = null)
    {
        // Cari status DRAFT, jika tidak ada, gunakan default (id=1 biasanya DRAFT)
        $statusDraft = StatusPengadaan::where('kode_status', 'DRAFT')->first();
        if (! $statusDraft) {
            $statusDraft = StatusPengadaan::firstOrCreate(
                ['kode_status' => 'DRAFT'],
                ['nama_status' => 'Draft']
            );
        }

        // Asumsi diajukan_oleh diisi dengan ID user sistem atau admin (misal id=1)
        // Dalam implementasi nyata, ini mungkin dibiarkan nullable sampai diproses purchasing

        $pengadaan = PengadaanBahan::create([
            'nomor_pengadaan' => 'PO-'.date('YmdHis').'-'.strtoupper(Str::random(4)),
            'jenis_pengadaan' => $jenis,
            'pesanan_id' => $pesanan_id,
            'diajukan_oleh' => 1, // Sistem/Admin ID
            'status_pengadaan_id' => $statusDraft->id,
            'tanggal_pengadaan' => date('Y-m-d'),
            'catatan' => $jenis == 'CATERING' ? 'Draft otomatis kebutuhan Catering' : 'Draft otomatis Stok Minimum',
        ]);

        foreach ($bahanList as $bahan) {
            $bb = BahanBaku::find($bahan['bahan_baku_id']);
            if ($bb) {
                DetailPengadaanBahan::create([
                    'pengadaan_bahan_id' => $pengadaan->id,
                    'bahan_baku_id' => $bb->id,
                    'jumlah_dipesan' => $bahan['jumlah_kurang'],
                    'satuan_id' => $bb->satuan_id,
                    'harga_satuan' => 0, // Akan diupdate oleh staf purchasing
                    'subtotal' => 0,
                ]);
            }
        }
    }
}
