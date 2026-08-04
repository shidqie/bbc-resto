<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\DetailPengadaanBahan;
use App\Models\Pesanan;
use App\Models\StokBahan;
use Illuminate\Support\Collection;

/**
 * Layanan pengadaan otomatis (FR-14).
 *
 * Rumus: jumlah pengadaan = kebutuhan produksi + stok pengaman - stok tersedia - jumlah sedang dipesan.
 *
 * Sumber usulan:
 *  - Kebutuhan bahan untuk pesanan Catering/Nasi Box mendatang.
 *  - Stok di bawah batas minimum.
 *  - Gabungan kebutuhan beberapa pesanan pada periode yang sama.
 *
 * Pengadaan dipisah menjadi Harian dan Catering:
 *  - Pengadaan Harian  : Dine-In, Nasi Box, stok Harian menipis → menambah stok Harian.
 *  - Pengadaan Catering: kekurangan pesanan Catering, stok Catering menipis → menambah stok Catering.
 *
 * Usulan tidak langsung menambah stok dan tetap harus diverifikasi pemilik.
 */
class PengadaanService
{
    public function __construct(protected KebutuhanBahanService $kebutuhanBahanService)
    {
    }

    /**
     * Hitung usulan pengadaan gabungan untuk satu jenis persediaan.
     *
     * @param  string  $jenisPersediaan  'harian' | 'catering'
     * @return Collection<int, array{bahan_baku_id:int, kebutuhan_produksi:float, stok_pengaman:float, stok_tersedia:float, sedang_dipesan:float, usulan:float, cukup:bool}>
     */
    public function usulanGabungan(int $hariKeDepan = 7, string $jenisPersediaan = 'harian', ?array $jenisPesananIds = null): Collection
    {
        $kebutuhan = $this->kebutuhanProduksiMendatang($hariKeDepan, $jenisPersediaan, $jenisPesananIds);
        $sedangDipesan = $this->jumlahSedangDipesan($jenisPersediaan);

        $bahanIds = $kebutuhan->keys()
            ->merge($this->bahanMenipis($jenisPersediaan)->pluck('bahan_baku_id'))
            ->unique();

        $usulan = collect();
        foreach ($bahanIds as $bahanId) {
            $bahan = BahanBaku::find($bahanId);
            if (! $bahan) {
                continue;
            }

            $kebutuhanProduksi = (float) ($kebutuhan->get($bahanId) ?? 0);
            $stokTersedia = $this->stokPada($bahanId, $jenisPersediaan);
            $stokPengaman = $this->stokMinimalPada($bahanId, $jenisPersediaan);
            $sedang = (float) ($sedangDipesan->get($bahanId) ?? 0);

            $usulanQty = $kebutuhanProduksi + $stokPengaman - $stokTersedia - $sedang;

            $usulan->push([
                'bahan' => $bahan->load('satuan'),
                'bahan_baku_id' => $bahanId,
                'kebutuhan_produksi' => round($kebutuhanProduksi, 3),
                'stok_pengaman' => $stokPengaman,
                'stok_tersedia' => $stokTersedia,
                'sedang_dipesan' => $sedang,
                'usulan' => round(max(0, $usulanQty), 3),
                'cukup' => $usulanQty <= 0,
            ]);
        }

        return $usulan->sortByDesc('usulan')->values();
    }

    /**
     * Total kebutuhan bahan untuk pesanan mendatang pada satu jenis persediaan.
     *
     * - Harian  : Nasi Box (jenis_pesanan kode NASIBOX / id 3).
     * - Catering: Catering (jenis_pesanan kode CAT / id 2).
     *
     * @return Collection<int, float> key=bahan_baku_id
     */
    public function kebutuhanProduksiMendatang(int $hariKeDepan = 7, string $jenisPersediaan = 'harian', ?array $jenisPesananIds = null): Collection
    {
        $jenisIds = $jenisPesananIds ?? $this->defaultJenisPesananIds($jenisPersediaan);
        if (empty($jenisIds)) {
            return collect();
        }

        $dari = now()->startOfDay();
        $sampai = now()->addDays($hariKeDepan)->endOfDay();

        $pesananIds = Pesanan::whereIn('jenis_pesanan_id', $jenisIds)
            ->where('status_pesanan_id', '<', 5) // belum selesai/batal
            ->whereHas('jadwal_pesanan', function ($q) use ($dari, $sampai) {
                $q->whereBetween('tanggal_acara', [$dari, $sampai]);
            })
            ->pluck('id');

        if ($pesananIds->isEmpty()) {
            return collect();
        }

        $total = collect();
        foreach (Pesanan::with(['detail_pesanan.menu', 'detail_pesanan.pilihan_pesanan_catering'])
            ->whereIn('id', $pesananIds)->get() as $pesanan) {
            foreach ($this->kebutuhanBahanService->kebutuhanBahanPesanan($pesanan) as $item) {
                $total->put(
                    $item['bahan_baku_id'],
                    (float) ($total->get($item['bahan_baku_id']) ?? 0) + $item['kebutuhan']
                );
            }
        }

        return $total;
    }

    /**
     * Total jumlah yang sedang dipesan (dipesan - diterima) pada pengadaan
     * yang belum selesai, dibatasi jenis pengadaan (opsional).
     *
     * @return Collection<int, float> key=bahan_baku_id
     */
    public function jumlahSedangDipesan(?string $jenisPengadaan = null): Collection
    {
        $rows = DetailPengadaanBahan::whereHas('pengadaan_bahan', function ($q) use ($jenisPengadaan) {
            $q->whereIn('status_pengadaan_id', [1, 2, 3]); // belum SELESAI / belum DITERIMA
            if ($jenisPengadaan) {
                $q->where('jenis_pengadaan', $jenisPengadaan);
            }
        })
            ->get(['bahan_baku_id', 'jumlah_dipesan', 'jumlah_diterima'])
            ->groupBy('bahan_baku_id')
            ->map(function ($items) {
                return round($items->sum(fn ($i) => (float) $i->jumlah_dipesan - (float) $i->jumlah_diterima), 3);
            });

        return $rows;
    }

    /**
     * Daftar bahan yang stoknya di bawah (atau sama dengan) batas minimum
     * pada jenis persediaan tertentu.
     *
     * @return Collection<int, BahanBaku>
     */
    public function bahanMenipis(string $jenisPersediaan = 'harian'): Collection
    {
        return BahanBaku::with(['stok_bahans' => fn ($q) => $q->where('jenis_persediaan', $jenisPersediaan)])
            ->where('status_aktif', true)
            ->get()
            ->filter(function ($bahan) use ($jenisPersediaan) {
                $stokRow = $bahan->stok_bahans->firstWhere('jenis_persediaan', $jenisPersediaan);
                if (! $stokRow) {
                    return false;
                }

                return (float) $stokRow->jumlah_stok < (float) $stokRow->stok_minimal
                    && (float) $stokRow->stok_minimal > 0;
            })
            ->values();
    }

    /**
     * Saldo stok bahan pada jenis persediaan tertentu.
     */
    public function stokPada(int $bahanBakuId, string $jenisPersediaan): float
    {
        return (float) (StokBahan::where('bahan_baku_id', $bahanBakuId)
            ->where('jenis_persediaan', $jenisPersediaan)
            ->value('jumlah_stok') ?? 0);
    }

    /**
     * Stok minimal bahan pada jenis persediaan tertentu.
     */
    public function stokMinimalPada(int $bahanBakuId, string $jenisPersediaan): float
    {
        return (float) (StokBahan::where('bahan_baku_id', $bahanBakuId)
            ->where('jenis_persediaan', $jenisPersediaan)
            ->value('stok_minimal') ?? 0);
    }

    /**
     * Default id jenis_pesanan sesuai jenis persediaan.
     * - harian   : Nasi Box (kode NASIBOX). Bila tidak ditemukan, pakai id 3.
     * - catering : Catering (kode CAT/CATERING). Bila tidak ditemukan, pakai id 2.
     */
    protected function defaultJenisPesananIds(string $jenisPersediaan): array
    {
        $kode = $jenisPersediaan === 'catering' ? ['CAT', 'CATERING'] : ['NASIBOX'];

        $ids = \App\Models\JenisPesanan::whereIn('kode_jenis', $kode)->pluck('id')->all();

        return $ids ?: ($jenisPersediaan === 'catering' ? [2] : [3]);
    }
}
