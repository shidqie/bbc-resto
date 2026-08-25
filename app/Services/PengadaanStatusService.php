<?php

namespace App\Services;

use App\Models\DetailPengadaanBahan;
use App\Models\DetailPurchaseOrder;
use App\Models\PengadaanBahan;
use App\Models\PurchaseOrder;
use App\Models\StatusPengadaan;
use Illuminate\Support\Collection;

/**
 * Service perhitungan sisa kebutuhan & derivasi status modul Pengadaan.
 *
 * - Pemintaan (Permintaan) 1:N Purchase Order.
 * - Sisa kebutuhan dihitung dari selisih jumlah diminta dengan total diterima
 *   (akumulasi seluruh detail penerimaan dari semua PO permintaan tersebut).
 * - "Sudah di-PO" dihitung dari total jumlah_dipesan pada seluruh detail PO.
 */
class PengadaanStatusService
{
    /**
     * Hitung status tiap detail permintaan.
     *
     * @return array{bahan_baku_id:int, nama_bahan:string, jumlah_diminta:float,
     *          sudah_di_po:float, jumlah_diterima:float, sisa:float, status:string, warna:string}
     */
    public function sisaDetailPermintaan(DetailPengadaanBahan $detail): array
    {
        $diminta = (float) $detail->jumlah_dipesan;
        $sudahDiPo = (float) $detail->purchase_order_sum ?? (float) $detail->detail_purchase_order()->sum('jumlah_dipesan');
        $diterima = (float) $detail->jumlah_diterima;
        $sisa = max(0, $diminta - $diterima);

        $status = 'belum';
        $warna = 'warning';
        if ($diterima >= $diminta && $diminta > 0) {
            $status = 'terpenuhi';
            $warna = 'success';
        } elseif ($diterima > 0) {
            $status = 'sebagian';
            $warna = 'warning';
        }

        return [
            'detail_id' => $detail->id,
            'bahan_id' => $detail->bahan_baku_id,
            'nama_bahan' => optional($detail->bahan_baku)->nama_bahan,
            'satuan' => optional($detail->satuan)->singkatan ?? optional($detail->satuan)->nama_satuan ?? \App\Helpers\UnitHelper::getPurchasingUnit($detail->bahan_baku?->satuan),
            'jumlah_diminta' => $diminta,
            'sudah_di_po' => $sudahDiPo,
            'jumlah_diterima' => $diterima,
            'sisa' => $sisa,
            'status' => $status,
            'status_nama' => $status === 'terpenuhi' ? 'Terpenuhi' : ($status === 'sebagian' ? 'Sebagian' : 'Belum Terpenuhi'),
            'warna' => $warna,
        ];
    }

    /**
     * Daftar detail permintaan lengkap dengan status & sisa per item.
     *
     * @return Collection<int, array>
     */
    public function sisaPermintaan(PengadaanBahan $pengadaan): Collection
    {
        return $pengadaan->detail_pengadaan_bahan()
            ->with(['bahan_baku.satuan', 'satuan', 'detail_purchase_order'])
            ->get()
            ->map(fn ($d) => $this->sisaDetailPermintaan($d))
            ->values();
    }

    /**
     * Derivasi status permintaan berdasarkan keberadaan PO & progres penerimaan.
     */
    public function impliedStatusKode(PengadaanBahan $pengadaan): string
    {
        $kode = StatusPengadaan::kodeById($pengadaan->status_pengadaan_id);
        if ($kode === StatusPengadaan::DIBATALKAN) {
            return StatusPengadaan::DIBATALKAN;
        }

        $hasPo = $pengadaan->purchase_order()->where('status', '!=', PurchaseOrder::DIBATALKAN)->exists();
        if (! $hasPo) {
            return StatusPengadaan::MENUNGGU_PEMBELIAN;
        }

        $items = $this->sisaPermintaan($pengadaan);
        $semuaTerpenuhi = $items->isNotEmpty() && $items->every(fn ($i) => $i['status'] === 'terpenuhi');
        $adaTerima = $items->contains(fn ($i) => $i['jumlah_diterima'] > 0);

        if ($semuaTerpenuhi) {
            return StatusPengadaan::SELESAI;
        }
        if ($adaTerima) {
            return StatusPengadaan::DITERIMA_SEBAGIAN;
        }

        return StatusPengadaan::DALAM_PROSES;
    }

    /**
     * Status PO berdasarkan progres penerimaan detail-nya.
     */
    public function impliedPoStatus(PurchaseOrder $po): string
    {
        if ($po->status === PurchaseOrder::DIBATALKAN) {
            return PurchaseOrder::DIBATALKAN;
        }

        $details = $po->detail_purchase_order()->get();
        if ($details->isEmpty()) {
            return PurchaseOrder::MENUNGGU_BARANG;
        }

        $semuaTerpenuhi = $details->every(fn ($d) => (float) $d->jumlah_diterima >= (float) $d->jumlah_dipesan);
        $adaTerima = $details->contains(fn ($d) => (float) $d->jumlah_diterima > 0);

        if ($semuaTerpenuhi) {
            return PurchaseOrder::SELESAI;
        }
        if ($adaTerima) {
            return PurchaseOrder::DITERIMA_SEBAGIAN;
        }

        return PurchaseOrder::MENUNGGU_BARANG;
    }

    /**
     * Apakah permintaan ini masih punya sisa (bisa dibuat PO / PO lanjutan)?
     */
    public function masihPunyaSisa(PengadaanBahan $pengadaan): bool
    {
        return $this->sisaPermintaan($pengadaan)->contains(fn ($i) => $i['sisa'] > 0);
    }

    /**
     * Apakah Purchase Order masih bisa diterima (belum selesai)?
     */
    public function poMasihBisaDiterima(PurchaseOrder $po): bool
    {
        return $po->status === PurchaseOrder::MENUNGGU_BARANG;
    }

    /**
     * Sisa per baris PO yang masih bisa diterima.
     */
    public function sisaDetailPo(PurchaseOrder $po): Collection
    {
        return $po->detail_purchase_order()->with(['bahan_baku.satuan', 'satuan', 'detail_pengadaan_bahan'])->get()->map(function (DetailPurchaseOrder $d) {
            $satuanBeli = optional($d->satuan)->singkatan ?? optional($d->satuan)->nama_satuan ?? \App\Helpers\UnitHelper::getPurchasingUnit($d->bahan_baku?->satuan);
            $hargaSatuan = (float) ($d->detail_pengadaan_bahan?->harga_satuan ?? \App\Helpers\UnitHelper::toPurchasingPrice(optional($d->bahan_baku)->harga_satuan ?? 0, $d->bahan_baku?->satuan));

            return [
                'detail_id' => $d->id,
                'bahan_id' => $d->bahan_baku_id,
                'nama_bahan' => optional($d->bahan_baku)->nama_bahan,
                'kode_bahan' => optional($d->bahan_baku)->id_bahan_baku ?? optional($d->bahan_baku)->kode_bahan,
                'satuan' => $satuanBeli,
                'jumlah_dipesan' => (float) $d->jumlah_dipesan,
                'jumlah_diterima' => (float) $d->jumlah_diterima,
                'sisa' => (float) $d->sisa,
                'harga_satuan' => $hargaSatuan,
                'status_nama' => $d->status_nama,
                'warna' => $d->status_warna,
            ];
        })->values();
    }
}