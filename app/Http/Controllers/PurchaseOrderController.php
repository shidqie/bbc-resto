<?php

namespace App\Http\Controllers;

use App\Models\PengadaanBahan;
use App\Models\PurchaseOrder;
use App\Models\DetailPurchaseOrder;
use App\Models\StatusPengadaan;
use App\Services\PengadaanStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['pengadaan_bahan', 'detail_purchase_order.bahan_baku', 'dibuat_oleh_pengguna'])
            ->orderBy('tanggal_po', 'desc')
            ->orderByDesc('dibuat_pada');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhereHas('pengadaan_bahan', fn ($pq) => $pq->where('id_pengadaan', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', "%{$request->supplier}%");
        }

        if ($request->filled('dari')) {
            $query->whereDate('tanggal_po', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_po', '<=', $request->sampai);
        }

        $pos = $query->paginate(10)->withQueryString();

        $statuses = [
            PurchaseOrder::MENUNGGU_BARANG => 'Menunggu Barang',
            PurchaseOrder::DITERIMA_SEBAGIAN => 'Diterima Sebagian',
            PurchaseOrder::SELESAI => 'Selesai',
            PurchaseOrder::DIBATALKAN => 'Dibatalkan',
        ];

        return view('pengadaan.purchase-order.index', compact('pos', 'statuses'));
    }

    public function create(PengadaanBahan $pengadaan)
    {
        $statusService = app(PengadaanStatusService::class);
        abort_unless(! in_array($statusService->impliedStatusKode($pengadaan), [
            StatusPengadaan::SELESAI,
            StatusPengadaan::DIBATALKAN,
        ]), 403, 'Permintaan tidak dapat dibuatkan purchase order.');

        $items = $statusService->sisaPermintaan($pengadaan)
            ->where('sisa', '>', 0)
            ->values();

        if ($items->isEmpty()) {
            return redirect()->route('pengadaan.permintaan.show', $pengadaan)
                ->with('info', 'Semua kebutuhan pada permintaan ini sudah terpenuhi.');
        }

        $kodePo = $this->kodePo();

        return view('pengadaan.purchase-order.create', compact('pengadaan', 'items', 'kodePo'));
    }

    public function store(Request $request, PengadaanBahan $pengadaan)
    {
        $statusService = app(PengadaanStatusService::class);
        abort_unless(! in_array($statusService->impliedStatusKode($pengadaan), [
            StatusPengadaan::SELESAI,
            StatusPengadaan::DIBATALKAN,
        ]), 403, 'Permintaan tidak dapat dibuatkan purchase order.');

        $request->validate([
            'supplier' => 'required|string|max:150',
            'tanggal_po' => 'required|date',
            'catatan' => 'nullable|string',
            'item_checked' => 'required|array|min:1',
            'jumlah_pesanan' => 'required|array',
        ]);

        $statusService = app(PengadaanStatusService::class);
        $sisa = $statusService->sisaPermintaan($pengadaan);

        $checkeds = collect($request->item_checked)->filter(fn ($v) => $v)->keys()->all();

        $picked = $sisa->where('sisa', '>', 0)->whereIn('detail_id', $checkeds)->values();
        abort_unless($picked->isNotEmpty(), 422, 'Pilih minimal satu bahan yang masih memiliki sisa kebutuhan.');

        DB::transaction(function () use ($request, $picked, $pengadaan) {
            $po = PurchaseOrder::create([
                'nomor_po' => $this->kodePo(),
                'pengadaan_bahan_id' => $pengadaan->id,
                'supplier' => $request->supplier,
                'tanggal_po' => $request->tanggal_po,
                'status' => PurchaseOrder::MENUNGGU_BARANG,
                'catatan' => $request->catatan,
                'dibuat_oleh' => auth()->id() ?? 1,
            ]);

            foreach ($picked as $item) {
                $idKey = (string) $item['detail_id'];
                $diminta = (float) $item['sisa'];
                $jumlah = (float) str_replace(',', '.', $request->jumlah_pesanan[$idKey] ?? $diminta);
                $jumlah = max(0, $jumlah);

                if ($jumlah <= 0) {
                    continue;
                }

                $detailPermintaan = \App\Models\DetailPengadaanBahan::with('satuan')->find($item['detail_id']);

                DetailPurchaseOrder::create([
                    'purchase_order_id' => $po->id,
                    'detail_pengadaan_bahan_id' => $item['detail_id'],
                    'bahan_baku_id' => $item['bahan_id'],
                    'jumlah_dipesan' => $jumlah,
                    'jumlah_diterima' => 0,
                    'satuan_id' => $detailPermintaan?->satuan_id,
                ]);
            }

            // Update status permintaan → dalam proses.
            $pengadaan->status_pengadaan_id = StatusPengadaan::idByKode(StatusPengadaan::DALAM_PROSES);
            $pengadaan->save();
        });

        return redirect()->route('pengadaan.po.index')
            ->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function show(PurchaseOrder $po)
    {
        $po->load([
            'pengadaan_bahan.status_pengadaan',
            'pengadaan_bahan.diajukan_oleh_pengguna',
            'detail_purchase_order.bahan_baku.satuan',
            'penerimaan_bahan.diterima_oleh_pengguna',
        ]);

        $statusService = app(PengadaanStatusService::class);
        $sisaItems = $statusService->sisaDetailPo($po);

        return view('pengadaan.purchase-order.show', compact('po', 'sisaItems'));
    }

    public function print(PurchaseOrder $po)
    {
        $po->load(['pengadaan_bahan', 'pengadaan_bahan.diajukan_oleh_pengguna', 'detail_purchase_order.bahan_baku.satuan']);

        $pdf = Pdf::loadView('pengadaan.purchase-order.print', compact('po'));

        return $pdf->stream('Purchase-Order-' . $po->nomor_po . '.pdf');
    }

    public function cancel(PurchaseOrder $po)
    {
        abort_unless(in_array($po->status, [
            PurchaseOrder::MENUNGGU_BARANG,
            PurchaseOrder::DITERIMA_SEBAGIAN,
        ]), 403, 'Purchase Order tidak dapat dibatalkan.');

        $po->status = PurchaseOrder::DIBATALKAN;
        $po->save();

        $pengadaan = $po->pengadaan_bahan;
        $statusService = app(PengadaanStatusService::class);
        $kode = $statusService->impliedStatusKode($pengadaan->refresh());
        $pengadaan->status_pengadaan_id = StatusPengadaan::idByKode($kode);
        $pengadaan->save();

        return back()->with('success', 'Purchase Order dibatalkan.');
    }

    protected function kodePo(): string
    {
        $date = now()->format('Ymd');
        $count = PurchaseOrder::whereDate('dibuat_pada', now()->toDateString())->count() + 1;

        return 'PO-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}