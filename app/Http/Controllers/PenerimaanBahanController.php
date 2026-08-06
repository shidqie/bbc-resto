<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenerimaanBahan;
use App\Models\DetailPenerimaanBahan;
use App\Models\DetailPengadaanBahan;
use App\Models\PengadaanBahan;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

class PenerimaanBahanController extends Controller
{
    public function index(Request $request)
    {
        $pendingQuery = PengadaanBahan::with(['pemasok', 'status_pengadaan', 'detail_pengadaan_bahan.bahan_baku'])
            ->whereIn('status_pengadaan_id', [
                StatusPengadaan::idByKode(StatusPengadaan::MENUNGGU_PENERIMAAN),
                StatusPengadaan::idByKode(StatusPengadaan::DITERIMA_SEBAGIAN),
            ])
            ->orderBy('tanggal_pengadaan', 'desc');

        $riwayatQuery = PenerimaanBahan::with(['pengadaan_bahan', 'diterima_oleh_pengguna', 'detail_penerimaan_bahan'])
            ->orderBy('diterima_pada', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('nomor_pengadaan', 'like', "%{$search}%");
            });
            $riwayatQuery->where(function ($q) use ($search) {
                $q->where('nomor_penerimaan', 'like', "%{$search}%")
                  ->orWhere('kode_permintaan', 'like', "%{$search}%");
            });
        }

        $statusPenerimaan = ['menunggu_penerimaan', 'sedang_diperiksa', 'diterima_sebagian', 'selesai', 'ditolak'];
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'menunggu_penerimaan') {
                $riwayatQuery->whereIn('status', ['menunggu_penerimaan', 'sedang_diperiksa']);
            } else {
                $riwayatQuery->where('status', $status);
            }
        }

        $pending = $pendingQuery->get();
        $riwayat = $riwayatQuery->get();

        return view('inventory.pengadaan.penerimaan.index', compact('pending', 'riwayat', 'statusPenerimaan'));
    }

    public function create(Request $request)
    {
        $pilihan = PengadaanBahan::with(['detail_pengadaan_bahan.bahan_baku.satuan'])
            ->whereIn('status_pengadaan_id', [
                StatusPengadaan::idByKode(StatusPengadaan::MENUNGGU_PENERIMAAN),
                StatusPengadaan::idByKode(StatusPengadaan::DITERIMA_SEBAGIAN),
            ])
            ->orderBy('tanggal_pengadaan', 'desc')
            ->get();

        $pengadaan = null;
        $items = [];
        if ($request->filled('permintaan')) {
            $pengadaan = PengadaanBahan::with(['detail_pengadaan_bahan.bahan_baku.satuan', 'status_pengadaan'])
                ->findOrFail($request->permintaan);
            $kode = StatusPengadaan::kodeById($pengadaan->status_pengadaan_id);
            abort_unless(in_array($kode, [StatusPengadaan::MENUNGGU_PENERIMAAN, StatusPengadaan::DITERIMA_SEBAGIAN]), 403, 'Permintaan ini tidak menunggu penerimaan.');

            foreach ($pengadaan->detail_pengadaan_bahan as $detail) {
                $sisa = (float) $detail->jumlah_dipesan - (float) $detail->jumlah_diterima;
                if ($sisa > 0) {
                    $items[] = ['detail' => $detail, 'sisa' => $sisa];
                }
            }
        }

        $kodePenerimaan = $this->kodePenerimaan();

        return view('inventory.pengadaan.penerimaan.create', compact('pilihan', 'pengadaan', 'items', 'kodePenerimaan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pengadaan_bahan_id' => 'required|exists:pengadaan_bahan,id',
            'tanggal_penerimaan' => 'nullable|date',
            'supplier' => 'nullable|string|max:150',
            'nomor_nota' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'jumlah_diterima' => 'required|array|min:1',
            'kondisi' => 'required|array|min:1',
        ]);

        $pengadaan = PengadaanBahan::findOrFail($request->pengadaan_bahan_id);
        $kode = StatusPengadaan::kodeById($pengadaan->status_pengadaan_id);
        abort_unless(in_array($kode, [StatusPengadaan::MENUNGGU_PENERIMAAN, StatusPengadaan::DITERIMA_SEBAGIAN]), 403, 'Permintaan ini tidak menunggu penerimaan.');

        DB::transaction(function () use ($request, $pengadaan) {
            $penerimaan = PenerimaanBahan::create([
                'nomor_penerimaan' => $this->kodePenerimaan(),
                'pengadaan_bahan_id' => $pengadaan->id,
                'kode_permintaan' => $pengadaan->nomor_pengadaan,
                'diterima_oleh' => auth()->id() ?? 1,
                'diterima_pada' => $request->tanggal_penerimaan ? $request->tanggal_penerimaan . ' ' . now()->format('H:i:s') : now(),
                'supplier' => $request->supplier,
                'nomor_nota' => $request->nomor_nota,
                'status' => 'sedang_diperiksa',
                'catatan' => $request->catatan,
            ]);

            foreach ($request->jumlah_diterima as $detailId => $jumlah) {
                $detailPermintaan = DetailPengadaanBahan::with('bahan_baku')->find($detailId);
                if (! $detailPermintaan) continue;

                $diterima = (float) str_replace(',', '.', $jumlah);
                $diminta = max(0, (float) $detailPermintaan->jumlah_dipesan - (float) $detailPermintaan->jumlah_diterima);

                DetailPenerimaanBahan::create([
                    'penerimaan_bahan_id' => $penerimaan->id,
                    'detail_pengadaan_bahan_id' => $detailPermintaan->id,
                    'bahan_baku_id' => $detailPermintaan->bahan_baku_id,
                    'jumlah_diterima' => $diterima,
                    'jumlah_diminta' => $diminta,
                    'jumlah_kurang' => max(0, $diminta - $diterima),
                    'satuan_id' => $detailPermintaan->satuan_id,
                    'kondisi' => $request->kondisi[$detailId] ?? 'Baik',
                    'harga_satuan' => (float) $detailPermintaan->harga_satuan,
                    'catatan' => null,
                ]);
            }
        });

        return redirect()->route('pengadaan.penerimaan.index')
            ->with('success', 'Penerimaan bahan berhasil disimpan. Verifikasi untuk menambah stok.');
    }

    public function verify(Request $request, PenerimaanBahan $penerimaan)
    {
        if ($penerimaan->status === 'selesai' || $penerimaan->status === 'ditolak') {
            return back()->withErrors(['Verifikasi tidak dapat dilakukan untuk penerimaan ini.']);
        }

        $penerimaan->load(['detail_penerimaan_bahan.bahan_baku', 'pengadaan_bahan']);
        if (! $penerimaan->pengadaan_bahan) {
            return back()->withErrors(['Kode permintaan tidak tersedia atau tidak valid.']);
        }

        if ($penerimaan->detail_penerimaan_bahan->isEmpty() || $penerimaan->detail_penerimaan_bahan->every(fn($d) => (float) $d->jumlah_diterima <= 0)) {
            return back()->withErrors(['Jumlah diterima belum diisi.']);
        }

        if ($penerimaan->detail_penerimaan_bahan->some(fn($d) => ! in_array($d->kondisi, ['Baik', 'Rusak', 'Kurang']))) {
            return back()->withErrors(['Kondisi bahan belum dipilih.']);
        }

        if (blank($penerimaan->supplier) && blank($penerimaan->nomor_nota)) {
            return back()->withErrors(['Data supplier atau nomor nota harus diisi.']);
        }

        DB::transaction(function () use ($penerimaan) {
            $pengadaan = $penerimaan->pengadaan_bahan;
            $stokService = app(StockService::class);

            $anyKurang = false;
            foreach ($penerimaan->detail_penerimaan_bahan as $detail) {
                if ((float) $detail->jumlah_diterima <= 0) continue;

                $keterangan = "Penerimaan {$penerimaan->nomor_penerimaan} / {$penerimaan->kode_permintaan}";

                // Stok masuk dicatat ke setiap jenis persediaan (Harian & Catering).
                foreach ([StokBahan::JENIS_HARIAN, StokBahan::JENIS_CATERING] as $jenisPersediaan) {
                    $stokService->addStock(
                        $detail->bahan_baku_id,
                        (float) $detail->jumlah_diterima,
                        $keterangan,
                        1,
                        auth()->id(),
                        ['detail_penerimaan_bahan_id' => $detail->id],
                        $jenisPersediaan
                    );
                }

                $detailPermintaan = $detail->detail_pengadaan_bahan;
                if ($detailPermintaan) {
                    $detailPermintaan->jumlah_diterima = (float) $detailPermintaan->jumlah_diterima + (float) $detail->jumlah_diterima;
                    $detailPermintaan->save();
                }

                if ((float) $detail->jumlah_kurang > 0) {
                    $anyKurang = true;
                }
            }

            $statusPenerimaan = $anyKurang ? 'diterima_sebagian' : 'selesai';
            $penerimaan->status = $statusPenerimaan;
            $penerimaan->diverifikasi_oleh = auth()->id() ?? 1;
            $penerimaan->waktu_verifikasi = now();
            $penerimaan->save();

            $statusPengadaan = $anyKurang ? StatusPengadaan::DITERIMA_SEBAGIAN : StatusPengadaan::SELESAI;
            $pengadaan->status_pengadaan_id = StatusPengadaan::idByKode($statusPengadaan);
            $pengadaan->save();
        });

        return redirect()->route('pengadaan.penerimaan.index')
            ->with('success', 'Penerimaan berhasil diverifikasi. Stok bahan baku bertambah.');
    }

    public function show(PenerimaanBahan $penerimaan)
    {
        $penerimaan->load(['pengadaan_bahan', 'diterima_oleh_pengguna', 'diverifikasi_oleh_pengguna', 'detail_penerimaan_bahan.bahan_baku.satuan']);

        return view('inventory.pengadaan.penerimaan.show', compact('penerimaan'));
    }

    protected function kodePenerimaan(): string
    {
        $date = now();
        $count = PenerimaanBahan::whereDate('diterima_pada', $date->toDateString())->count() + 1;

        return 'PNR-' . $date->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
