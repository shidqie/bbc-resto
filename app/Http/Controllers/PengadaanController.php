<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengadaanBahan;
use App\Models\DetailPengadaanBahan;
use App\Models\BahanBaku;
use App\Models\JenisPesanan;
use App\Models\Pesanan;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use App\Services\KebutuhanBahanService;
use App\Services\PengadaanStatusService;
use Illuminate\Support\Facades\DB;

class PengadaanController extends Controller
{
    public function index(Request $request)
    {
        $query = PengadaanBahan::with(['diajukan_oleh_pengguna', 'status_pengadaan', 'detail_pengadaan_bahan'])
            ->orderBy('tanggal_pengadaan', 'desc')
            ->orderByDesc('dibuat_pada');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_pengadaan', 'like', "%{$search}%")
                    ->orWhereHas('pesanan', fn ($pq) => $pq->where('id_pesanan', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pengadaan', $request->jenis);
        }

        if ($request->filled('status')) {
            $kode = optional(StatusPengadaan::find($request->status))->kode_status;
            if ($kode && $kode === StatusPengadaan::MENUNGGU_PENERIMAAN) {
                $query->whereIn('status_pengadaan_id', [
                    StatusPengadaan::idByKode(StatusPengadaan::DALAM_PROSES),
                    StatusPengadaan::idByKode(StatusPengadaan::MENUNGGU_PENERIMAAN),
                ]);
            } else {
                $query->where('status_pengadaan_id', $request->status);
            }
        }

        if ($request->filled('dari')) {
            $query->whereDate('tanggal_pengadaan', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_pengadaan', '<=', $request->sampai);
        }

        $pengadaans = $query->paginate(10)->withQueryString();
        $statuses = StatusPengadaan::all();

        return view('admin.pengadaan.permintaan.index', compact('pengadaans', 'statuses'));
    }

    public function createHarian()
    {
        return $this->formPermintaan('harian');
    }

    public function createCatering(Request $request)
    {
        $pesanan = null;
        $items = collect();
        $error = null;
        $jenisCatering = JenisPesanan::where('kode_jenis', 'CAT')->value('id');

        if ($request->filled('pesanan_id') || $request->filled('kode_pesanan')) {
            $query = Pesanan::with(['detail_pesanan.menu', 'detail_pesanan.pilihan_pesanan_catering'])
                ->where('jenis_pesanan_id', $jenisCatering)
                ->whereNotIn('status_pesanan_id', [1, 6]);

            if ($request->filled('pesanan_id')) {
                $pesanan = (clone $query)->find($request->pesanan_id);
            } else {
                $pesanan = (clone $query)->where('id_pesanan', trim($request->kode_pesanan))->first();
            }

            if (! $pesanan) {
                $error = 'Kode pesanan katering tidak ditemukan atau belum dapat dibuatkan permintaan.';
            } else {
                $kebutuhan = app(KebutuhanBahanService::class)->kebutuhanBahanPesanan($pesanan);
                $bahanIds = $kebutuhan->pluck('bahan_baku_id')->unique();
                $bahans = BahanBaku::with('satuan')->whereIn('id', $bahanIds)->get()->keyBy('id');
                $stoks = StokBahan::where('jenis_persediaan', StokBahan::JENIS_CATERING)
                    ->whereIn('bahan_baku_id', $bahanIds)
                    ->get()->keyBy('bahan_baku_id');

                $items = $kebutuhan->map(function ($row) use ($bahans, $stoks) {
                    $bahan = $bahans->get($row['bahan_baku_id']);
                    $stok = $stoks->get($row['bahan_baku_id']);

                    return [
                        'bahan_baku' => $bahan,
                        'kebutuhan' => (float) $row['kebutuhan'],
                        'stok_saat_ini' => (float) ($stok->jumlah_stok ?? 0),
                        'stok_minimum' => (float) ($bahan->stok_minimal ?? $stok->stok_minimal ?? 0),
                    ];
                })->values();
            }
        }

        $daftarPesanan = Pesanan::where('jenis_pesanan_id', $jenisCatering)
            ->whereNotIn('status_pesanan_id', [1, 6])
            ->orderBy('tanggal_pesanan', 'desc')
            ->get(['id', 'id_pesanan', 'tanggal_pesanan', 'status_pesanan_id']);

        $kodePreview = $this->kodePermintaan();

        return view('admin.pengadaan.permintaan.catering-create', compact('pesanan', 'items', 'daftarPesanan', 'kodePreview', 'error'));
    }

    protected function formPermintaan(string $jenis)
    {
        $semuaBahan = StokBahan::with(['bahan_baku.satuan'])
            ->where('jenis_persediaan', $jenis)
            ->get();

        // Ambil max kebutuhan per bahan baku untuk cek stok kritis (tanpa heavy join)
        $maxKebutuhanMap = DB::table('resep_menu')
            ->select('bahan_baku_id', DB::raw('MAX(jumlah) as max_kebutuhan'))
            ->groupBy('bahan_baku_id')
            ->pluck('max_kebutuhan', 'bahan_baku_id');

        $bahanMenipisCount = 0;
        foreach ($semuaBahan as $stok) {
            $maxKebutuhan = (float) ($maxKebutuhanMap[$stok->bahan_baku_id] ?? 0);
            $stokMinimal = (float) $stok->bahan_baku->stok_minimal;
            
            // Adjust stok minimal di memory jika max kebutuhan lebih besar
            if ($maxKebutuhan > $stokMinimal) {
                $stok->bahan_baku->stok_minimal = $maxKebutuhan;
                $stokMinimal = $maxKebutuhan;
            }

            if ((float) $stok->jumlah_stok <= $stokMinimal) {
                $bahanMenipisCount++;
            }
        }

        // $semuaBahan is already queried above

        $formRoute = $jenis === 'harian' ? 'pengadaan.harian.store' : 'pengadaan.catering.store';
        $kodePreview = $this->kodePermintaan();

        return view('admin.pengadaan.permintaan.harian-create', compact('jenis', 'formRoute', 'bahanMenipisCount', 'semuaBahan', 'kodePreview'));
    }

    public function storeHarian(Request $request)
    {
        return $this->storePermintaan($request, 'harian');
    }

    public function storeCatering(Request $request)
    {
        $jenisCatering = JenisPesanan::where('kode_jenis', 'CAT')->value('id');
        $pesanan = $request->filled('pesanan_id')
            ? Pesanan::where('jenis_pesanan_id', $jenisCatering)->find($request->pesanan_id)
            : null;
        abort_unless($pesanan, 422, 'Pesanan katering tidak valid.');

        return $this->storePermintaan($request, 'catering', $pesanan->id);
    }

    protected function storePermintaan(Request $request, string $jenis, ?int $pesananId = null)
    {
        $request->validate([
            'tanggal_pengadaan' => 'required|date',
            'catatan' => 'nullable|string',
            'pesanan_id' => 'nullable|exists:pesanan,id',
            'bahan_id' => 'required|array|min:1',
            'jumlah' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $jenis, $pesananId) {
            $pengadaan = PengadaanBahan::create([
                
                'pesanan_id' => $pesananId,
                'diajukan_oleh' => auth()->id() ?? 1,
                'status_pengadaan_id' => StatusPengadaan::idByKode(StatusPengadaan::MENUNGGU_PEMBELIAN),
                'jenis_pengadaan' => $jenis,
                'tanggal_pengadaan' => $request->tanggal_pengadaan,
                'catatan' => $request->catatan,
            ]);

            foreach ($request->bahan_id as $bahanId) {
                $bahan = BahanBaku::find($bahanId);
                // Kita harus mencari index bahan_id ini di array input. Karena bisa saja $request->bahan_id bentuknya flat array,
                // tapi jumlah menggunakan index dari $idx loop di frontend.
                // Solusi paling aman: ubah frontend agar name=\"jumlah[{{ $bahan->id }}]\" dan name=\"bahan_id[]\" value=\"{{ $bahan->id }}\".
                // Namun karena frontend masih mengirim jumlah berdasarkan index loop (0,1,2...), dan bahan_id hanya mengirim yang dicentang.
                // Oh wait, in HTML: name=\"jumlah[{{ $idx }}]\", name=\"bahan_id[]\". This is broken if a user unchecks a row.
                // Let's modify the frontend to use bahan->id as the key!
                
                if (! $bahan || ! isset($request->jumlah[$bahanId])) {
                    continue;
                }

                $jumlah = (float) str_replace(',', '.', $request->jumlah[$bahanId]);
                if ($jumlah <= 0) {
                    continue;
                }

                $stokBahan = StokBahan::where('bahan_baku_id', $bahan->id)
                    ->where('jenis_persediaan', $jenis)
                    ->first();

                DetailPengadaanBahan::create([
                    'pengadaan_bahan_id' => $pengadaan->id,
                    'bahan_baku_id' => $bahan->id,
                    'jumlah_dipesan' => $jumlah,
                    'stok_saat_ini' => (float) ($stokBahan->jumlah_stok ?? 0),
                    'stok_minimum' => (float) ($bahan->stok_minimal ?? $stokBahan->stok_minimal ?? 0),
                    'satuan_id' => $bahan->satuan_id,
                    'harga_satuan' => (float) $bahan->harga_satuan ?? 0,
                    'subtotal' => $jumlah * ((float) $bahan->harga_satuan ?? 0),
                ]);
            }
        });

        return redirect()->route('pengadaan.permintaan.index')
            ->with('success', 'Permintaan ' . ($jenis === 'harian' ? 'harian' : 'catering') . ' berhasil dibuat.');
    }

    public function show(PengadaanBahan $pengadaan)
    {
        $pengadaan->load([
            'diajukan_oleh_pengguna',
            'status_pengadaan',
            'purchase_order' => fn ($q) => $q->with(['penerimaan_bahan.diterima_oleh_pengguna']),
        ]);

        $statusService = app(PengadaanStatusService::class);
        $items = $statusService->sisaPermintaan($pengadaan);
        $sisaItems = $items->where('sisa', '>', 0)->values();

        $summary = [
            'total_bahan' => $items->count(),
            'terpenuhi' => $items->where('status', 'terpenuhi')->count(),
            'belum' => $items->where('status', '!=', 'terpenuhi')->count(),
            'jumlah_po' => $pengadaan->purchase_order->count(),
        ];

        return view('admin.pengadaan.permintaan.show', compact('pengadaan', 'items', 'sisaItems', 'summary'));
    }

    public function cancel(PengadaanBahan $pengadaan)
    {
        $kode = StatusPengadaan::kodeById($pengadaan->status_pengadaan_id);
        abort_unless(in_array($kode, [
            StatusPengadaan::DRAFT,
            StatusPengadaan::MENUNGGU_PEMBELIAN,
            StatusPengadaan::DALAM_PROSES,
            StatusPengadaan::MENUNGGU_PENERIMAAN,
            StatusPengadaan::DITERIMA_SEBAGIAN,
        ]), 403, 'Permintaan tidak dapat dibatalkan.');

        // PO yang masih berjalan ikut dibatalkan.
        DB::transaction(function () use ($pengadaan) {
            $pengadaan->purchase_order()
                ->whereIn('status', ['menunggu_barang', 'diterima_sebagian'])
                ->update(['status' => \App\Models\PurchaseOrder::DIBATALKAN]);

            $pengadaan->status_pengadaan_id = StatusPengadaan::idByKode(StatusPengadaan::DIBATALKAN);
            $pengadaan->save();
        });

        return back()->with('success', 'Permintaan dibatalkan.');
    }

    protected function kodePermintaan(): string
    {
        $date = now();
        $count = PengadaanBahan::whereDate('dibuat_pada', $date->toDateString())->count() + 1;

        return 'PRM-' . $date->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}