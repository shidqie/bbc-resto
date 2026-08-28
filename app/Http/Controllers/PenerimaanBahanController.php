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

        return view('admin.pengadaan.penerimaan.index', compact('penerimaans', 'statuses'));
    }

    public function create(PurchaseOrder $po)
    {
        if (!app(PengadaanStatusService::class)->poMasihBisaDiterima($po)) {
            return redirect()->route('pengadaan.po.show', $po->id)
                ->with('info', 'Seluruh bahan baku pada Purchase Order #' . $po->nomor_po . ' telah selesai diterima.');
        }

        $po->load([
            'pengadaan_bahan',
            'pengadaan_bahan.status_pengadaan',
            'detail_purchase_order.bahan_baku.satuan',
        ]);

        $statusService = app(PengadaanStatusService::class);
        $items = $statusService->sisaDetailPo($po)->where('sisa', '>', 0)->values();

        $kodePenerimaan = $this->kodePenerimaan();

        return view('admin.pengadaan.penerimaan.create', compact('po', 'items', 'kodePenerimaan'));
    }

    public function store(Request $request, PurchaseOrder $po)
    {
        if (!app(PengadaanStatusService::class)->poMasihBisaDiterima($po)) {
            return redirect()->route('pengadaan.po.show', $po->id)
                ->with('info', 'Purchase Order #' . $po->nomor_po . ' sudah selesai diterima.');
        }

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
        $pesanan = null;
        if ($po->kode_pesanan_catering) {
            $pesanan = \App\Models\Pesanan::where('id_pesanan', $po->kode_pesanan_catering)->first() ?: \App\Models\Pesanan::find($po->kode_pesanan_catering);
        }
        if (!$pesanan && $pengadaan && $pengadaan->pesanan_id) {
            $pesanan = $pengadaan->pesanan;
        }

        $isNasiBox = $pesanan && (int) $pesanan->jenis_pesanan_id === 3;
        $isDineIn = $pesanan && (int) $pesanan->jenis_pesanan_id === 1;

        $isCatering = (strtolower($po->jenis_po ?? '') === 'catering') || ($pengadaan && strtolower($pengadaan->jenis_pengadaan ?? '') === 'catering');
        if ($isNasiBox || $isDineIn) {
            $isCatering = false;
        }

        $jenisPersediaan = $isCatering ? StokBahan::JENIS_CATERING : StokBahan::JENIS_HARIAN;
        $stokService = app(StockService::class);

        $penerimaan = DB::transaction(function () use ($request, $po, $pengadaan, $jenisPersediaan, $stokService, $sisaItems, $checkeds) {
            $totalNotaRaw = (string) ($request->total_nota ?? '');
            $totalPembelian = null;
            if ($totalNotaRaw !== '') {
                if (str_contains($totalNotaRaw, ',')) {
                    $clean = str_replace('.', '', $totalNotaRaw);
                    $clean = str_replace(',', '.', $clean);
                    $totalPembelian = (float) preg_replace('/[^0-9.]/', '', $clean);
                } else {
                    $totalPembelian = (float) preg_replace('/[^0-9]/', '', $totalNotaRaw);
                }
            }

            $penerimaan = PenerimaanBahan::create([
                'nomor_penerimaan' => $this->kodePenerimaan(),
                'purchase_order_id' => $po->id,
                'kode_permintaan' => optional($pengadaan)->id_pengadaan ?? $po->nomor_po,
                'diterima_oleh' => auth()->id() ?? 1,
                'diterima_pada' => $request->tanggal_penerimaan
                    ? \Carbon\Carbon::parse($request->tanggal_penerimaan . ' ' . now()->format('H:i:s'))
                    : now(),
                'supplier' => $po->supplier,
                'nomor_nota' => $request->nomor_nota,
                'total_pembelian' => $totalPembelian,
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

                $raw = (string) ($request->jumlah_diterima[$detailId] ?? '0');
                if (str_contains($raw, ',')) {
                    $raw = str_replace('.', '', $raw);
                    $raw = str_replace(',', '.', $raw);
                }
                $diterima = (float) $raw;
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

                // Pengguna dapat menginput harga satuan aktual pada masing-masing bahan
                $hargaBeliRaw = (string) ($request->harga_beli[$detailId] ?? '');
                if ($hargaBeliRaw !== '') {
                    if (str_contains($hargaBeliRaw, ',')) {
                        $cleanHrg = str_replace('.', '', $hargaBeliRaw);
                        $cleanHrg = str_replace(',', '.', $cleanHrg);
                        $hargaBeli = (float) preg_replace('/[^0-9.]/', '', $cleanHrg);
                    } else {
                        $hargaBeli = (float) preg_replace('/[^0-9]/', '', $hargaBeliRaw);
                    }
                } else {
                    $hargaBeli = (float) ($detailPo->detail_pengadaan_bahan?->harga_satuan ?? $detailPo->harga_satuan ?? 0);
                }

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

                // Konversi kuantitas dari Purchasing Unit (Kg, Liter, Pcs) ke Base Unit Stok (Gram, Ml, Pcs)
                $jumlahStokMasuk = \App\Helpers\UnitHelper::toBaseQuantity($diterimaFinal, $detailPo->satuan);
                $satuanBeliName = \App\Helpers\UnitHelper::getPurchasingUnit($detailPo->satuan);

                // Stok masuk hanya pada jenis persediaan sesuai permintaan (harian/catering).
                $keterangan = "Penerimaan {$penerimaan->nomor_penerimaan} / {$po->nomor_po} (+{$diterimaFinal} {$satuanBeliName})";
                $stokService->addStock(
                    $detailPo->bahan_baku_id,
                    $jumlahStokMasuk,
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

            // Jika total pembelian tidak diisi secara manual, otomatis hitung dari akumulasi detail bahan
            if ($totalPembelian === null || $totalPembelian <= 0) {
                $penerimaan->total_pembelian = (float) $penerimaan->detail_penerimaan_bahan()->sum(\Illuminate\Support\Facades\DB::raw('jumlah_diterima * harga_satuan'));
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

            return $penerimaan;
        });

        return redirect()->route('pengadaan.po.show', $po->id)
            ->with('success', 'Bahan baku berhasil diterima dan stok persediaan berhasil ditambahkan!')
            ->with('penerimaan_berhasil', true)
            ->with('penerimaan_nomor', optional($penerimaan)->nomor_penerimaan ?? $po->nomor_po);
    }

    public function show(PenerimaanBahan $penerimaan)
    {
        $penerimaan->load([
            'purchase_order.pengadaan_bahan',
            'diterima_oleh_pengguna',
            'detail_penerimaan_bahan.bahan_baku.satuan',
        ]);

        $total_pembelian = ($penerimaan->total_pembelian && (float)$penerimaan->total_pembelian > 0)
            ? (float) $penerimaan->total_pembelian
            : $penerimaan->detail_penerimaan_bahan->sum(fn ($d) => (float) $d->jumlah_diterima * (float) $d->harga_satuan);
        
        $sisaItems = collect();
        if ($penerimaan->purchase_order_id) {
            $sisaItems = app(PengadaanStatusService::class)
                ->sisaDetailPo($penerimaan->purchase_order)
                ->where('sisa', '>', 0)
                ->values();
        }

        return view('admin.pengadaan.penerimaan.show', compact('penerimaan', 'total_pembelian', 'sisaItems'));
    }

    protected function kodePenerimaan(): string
    {
        $date = now();
        $count = PenerimaanBahan::whereDate('diterima_pada', $date->toDateString())->count() + 1;

        return 'PNR-' . $date->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}