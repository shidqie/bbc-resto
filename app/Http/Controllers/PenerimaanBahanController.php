<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanBahan;
use App\Models\DetailPenerimaanBahan;
use App\Models\DetailPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use App\Services\PengadaanStatusService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanBahanController extends Controller
{
    public function index(Request $request)
    {
        $query = PenerimaanBahan::with([
            'purchase_order.pengadaan_bahan',
            'diterima_oleh_pengguna',
            'detail_penerimaan_bahan',
        ])->orderBy('diterima_pada', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_penerimaan', 'like', "%{$search}%")
                    ->orWhere('kode_permintaan', 'like', "%{$search}%")
                    ->orWhereHas('purchase_order', fn ($pq) => $pq->where('nomor_po', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', "%{$request->supplier}%");
        }

        if ($request->filled('dari')) {
            $query->whereDate('diterima_pada', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('diterima_pada', '<=', $request->sampai);
        }

        $penerimaans = $query->paginate(10)->withQueryString();

        $statuses = [
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
        ];

        return view('pengadaan.penerimaan.index', compact('penerimaans', 'statuses'));
    }

    public function create(PurchaseOrder $po)
    {
        abort_unless(app(PengadaanStatusService::class)->poMasihBisaDiterima($po), 403, 'Purchase Order ini tidak dapat diterima (sudah selesai/dibatalkan).');

        $po->load([
            'pengadaan_bahan',
            'pengadaan_bahan.status_pengadaan',
            'detail_purchase_order.bahan_baku.satuan',
        ]);

        $statusService = app(PengadaanStatusService::class);
        $items = $statusService->sisaDetailPo($po)->where('sisa', '>', 0)->values();

        $kodePenerimaan = $this->kodePenerimaan();

        return view('pengadaan.penerimaan.create', compact('po', 'items', 'kodePenerimaan'));
    }

    public function store(Request $request, PurchaseOrder $po)
    {
        abort_unless(app(PengadaanStatusService::class)->poMasihBisaDiterima($po), 403, 'Purchase Order ini tidak dapat diterima (sudah selesai/dibatalkan).');

        $request->validate([
            'tanggal_penerimaan' => 'nullable|date',
            'nomor_nota' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'item_checked' => 'required|array|min:1',
            'jumlah_diterima' => 'required|array',
            'harga_beli' => 'nullable|array',
            'kondisi' => 'nullable|array',
        ]);

        $statusService = app(PengadaanStatusService::class);
        $sisaItems = $statusService->sisaDetailPo($po)->where('sisa', '>', 0)->keyBy('detail_id');

        $checkeds = collect($request->item_checked)->filter(fn ($v) => $v)->keys()->all();

        $pengadaan = $po->pengadaan_bahan;
        $jenisPersediaan = $pengadaan->jenis_pengadaan === 'catering' ? StokBahan::JENIS_CATERING : StokBahan::JENIS_HARIAN;
        $stokService = app(StockService::class);

        DB::transaction(function () use ($request, $po, $pengadaan, $jenisPersediaan, $stokService, $sisaItems, $checkeds) {
            $penerimaan = PenerimaanBahan::create([
                'nomor_penerimaan' => $this->kodePenerimaan(),
                'purchase_order_id' => $po->id,
                'kode_permintaan' => $pengadaan->id_pengadaan,
                'diterima_oleh' => auth()->id() ?? 1,
                'diterima_pada' => $request->tanggal_penerimaan
                    ? \Carbon\Carbon::parse($request->tanggal_penerimaan . ' ' . now()->format('H:i:s'))
                    : now(),
                'supplier' => $po->supplier,
                'nomor_nota' => $request->nomor_nota,
                'status' => 'diproses',
                'catatan' => $request->catatan,
            ]);

            $anyKurang = false;
            $totalDiterima = 0;

            // Preload details to avoid N+1 queries
            $detailsPo = DetailPurchaseOrder::with(['bahan_baku', 'detail_pengadaan_bahan'])
                ->whereIn('id', $checkeds)
                ->get()
                ->keyBy('id');

            foreach ($sisaItems as $detailId => $item) {
                if (! in_array($detailId, $checkeds)) {
                    continue;
                }

                $raw = $request->jumlah_diterima[$detailId] ?? 0;
                $diterima = (float) str_replace(',', '.', $raw);
                if ($diterima < 0) {
                    $diterima = 0;
                }

                $detailPo = $detailsPo->get($item['detail_id']);
                if (! $detailPo) {
                    continue;
                }

                $sisaPo = (float) $detailPo->sisa;
                $diterimaFinal = max(0, min($diterima, $sisaPo));

                if ($diterimaFinal <= 0) {
                    $anyKurang = true;
                    continue;
                }

                $hargaRaw = isset($request->harga_beli[$detailId]) && $request->harga_beli[$detailId] !== ''
                    ? $request->harga_beli[$detailId]
                    : (float) ($detailPo->detail_pengadaan_bahan?->harga_satuan ?? 0);

                $hargaBeli = is_numeric($hargaRaw)
                    ? (float) $hargaRaw
                    : (float) str_replace(['Rp', '.', ' '], '', $hargaRaw);

                $diminta = (float) $detailPo->jumlah_dipesan;

                $detailPenerimaan = DetailPenerimaanBahan::create([
                    'penerimaan_bahan_id' => $penerimaan->id,
                    'detail_purchase_order_id' => $detailPo->id,
                    'bahan_baku_id' => $detailPo->bahan_baku_id,
                    'jumlah_diterima' => $diterimaFinal,
                    'jumlah_diminta' => $diminta,
                    'jumlah_kurang' => max(0, $diminta - $diterimaFinal),
                    'satuan_id' => $detailPo->satuan_id,
                    'kondisi' => $request->kondisi[$detailId] ?? 'Baik',
                    'harga_satuan' => $hargaBeli,
                    'nama_supplier' => $po->supplier,
                ]);

                // Update jumlah diterima pada baris PO.
                $detailPo->jumlah_diterima = (float) $detailPo->jumlah_diterima + $diterimaFinal;
                $detailPo->save();

                // Stok masuk hanya pada jenis persediaan sesuai permintaan (harian/catering).
                $keterangan = "Penerimaan {$penerimaan->nomor_penerimaan} / {$po->nomor_po}";
                $stokService->addStock(
                    $detailPo->bahan_baku_id,
                    $diterimaFinal,
                    $keterangan,
                    1,
                    auth()->id(),
                    ['detail_penerimaan_bahan_id' => $detailPenerimaan->id],
                    $jenisPersediaan
                );

                // Akumulasi jumlah diterima pada detail permintaan.
                $detailPermintaan = $detailPo->detail_pengadaan_bahan;
                if ($detailPermintaan) {
                    $detailPermintaan->jumlah_diterima = (float) $detailPermintaan->jumlah_diterima + $diterimaFinal;
                    $detailPermintaan->save();
                }

                $totalDiterima += $diterimaFinal;

                if ($diterimaFinal < $diminta) {
                    $anyKurang = true;
                }
            }

            if ($totalDiterima <= 0) {
                // Tidak ada barang yang benar-benar diterima → tetap selesai agar tidak diblokir double.
                $penerimaan->status = 'selesai';
            } else {
                $penerimaan->status = $anyKurang ? 'diproses' : 'selesai';
            }
            $penerimaan->diverifikasi_oleh = auth()->id() ?? 1;
            $penerimaan->waktu_verifikasi = now();
            $penerimaan->save();

            // Update status PO & atau permintaan via derived status.
            $po->refresh();
            $poStatus = app(PengadaanStatusService::class)->impliedPoStatus($po);
            $po->status = $poStatus;
            $po->save();

            $pengadaan->refresh();
            $kode = app(PengadaanStatusService::class)->impliedStatusKode($pengadaan);
            $pengadaan->status_pengadaan_id = \App\Models\StatusPengadaan::idByKode($kode);
            $pengadaan->save();
        });

        return redirect()->route('pengadaan.penerimaan.index')
            ->with('success', 'Penerimaan berhasil disimpan. Stok bahan yang diterima telah diperbarui.');
    }

    public function show(PenerimaanBahan $penerimaan)
    {
        $penerimaan->load([
            'purchase_order.pengadaan_bahan',
            'diterima_oleh_pengguna',
            'detail_penerimaan_bahan.bahan_baku.satuan',
        ]);

        // Perubahan di baris ini: $totalPembelian diubah menjadi $total_pembelian
        $total_pembelian = $penerimaan->detail_penerimaan_bahan->sum(fn ($d) => (float) $d->jumlah_diterima * (float) $d->harga_satuan);
        
        $sisaItems = collect();
        if ($penerimaan->purchase_order_id) {
            $sisaItems = app(PengadaanStatusService::class)
                ->sisaDetailPo($penerimaan->purchase_order)
                ->where('sisa', '>', 0)
                ->values();
        }

        return view('pengadaan.penerimaan.show', compact('penerimaan', 'total_pembelian', 'sisaItems'));
    }

    protected function kodePenerimaan(): string
    {
        $date = now();
        $count = PenerimaanBahan::whereDate('diterima_pada', $date->toDateString())->count() + 1;

        return 'PNR-' . $date->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}