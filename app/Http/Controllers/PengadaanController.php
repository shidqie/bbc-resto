<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengadaanBahan;
use App\Models\DetailPengadaanBahan;
use App\Models\BahanBaku;
use App\Models\Pesanan;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PengadaanController extends Controller
{
    public function index(Request $request)
    {
        $query = PengadaanBahan::with(['diajukan_oleh_pengguna', 'status_pengadaan', 'detail_pengadaan_bahan'])
            ->orderBy('dibuat_pada', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengadaan', 'like', "%{$search}%")
                  ->orWhereHas('pesanan', function($q) use ($search) {
                      $q->where('nomor_pesanan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pengadaan', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status_pengadaan_id', $request->status);
        }

        if ($request->filled('periode')) {
            $periode = $request->periode;
            if ($periode == 'hari_ini') {
                $query->whereDate('tanggal_pengadaan', today());
            } elseif ($periode == 'minggu_ini') {
                $query->whereBetween('tanggal_pengadaan', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($periode == 'bulan_ini') {
                $query->whereMonth('tanggal_pengadaan', now()->month)
                      ->whereYear('tanggal_pengadaan', now()->year);
            }
        }

        $pengadaans = $query->paginate(10)->withQueryString();
        $statuses = StatusPengadaan::all();

        return view('inventory.pengadaan.index', compact('pengadaans', 'statuses'));
    }

    public function createHarian()
    {
        return $this->formPermintaan('harian');
    }

    public function createCatering(Request $request)
    {
        return $this->formPermintaan('catering');
    }

    protected function formPermintaan(string $jenis)
    {
        $stokMenipis = StokBahan::with(['bahan_baku.satuan'])
            ->where('jenis_persediaan', $jenis)
            ->join('bahan_baku', 'stok_bahan.bahan_baku_id', '=', 'bahan_baku.id')
            ->whereColumn('stok_bahan.jumlah_stok', '<=', 'bahan_baku.stok_minimal')
            ->select('stok_bahan.*')
            ->get();

        $semuaBahan = StokBahan::with(['bahan_baku.satuan'])
            ->where('jenis_persediaan', $jenis)
            ->get();

        $formRoute = $jenis === 'harian' ? 'pengadaan.harian.store' : 'pengadaan.catering.store';
        $kodePreview = $this->kodePermintaan();

        return view('inventory.pengadaan.create', compact('jenis', 'formRoute', 'stokMenipis', 'semuaBahan', 'kodePreview'));
    }

    public function storeHarian(Request $request)
    {
        return $this->storePermintaan($request, 'harian');
    }

    public function storeCatering(Request $request)
    {
        return $this->storePermintaan($request, 'catering');
    }

    protected function storePermintaan(Request $request, string $jenis)
    {
        $request->validate([
            'tanggal_pengadaan' => 'required|date',
            'catatan' => 'nullable|string',
            'bahan_id' => 'required|array|min:1',
            'jumlah' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $jenis) {
            $pengadaan = PengadaanBahan::create([
                'nomor_pengadaan' => $this->kodePermintaan(),
                'diajukan_oleh' => auth()->id() ?? 1,
                'status_pengadaan_id' => StatusPengadaan::idByKode(StatusPengadaan::MENUNGGU_PEMBELIAN),
                'jenis_pengadaan' => $jenis,
                'tanggal_pengadaan' => $request->tanggal_pengadaan,
                'catatan' => $request->catatan,
            ]);

            foreach ($request->bahan_id as $index => $bahanId) {
                $bahan = BahanBaku::find($bahanId);
                if ($bahan && isset($request->jumlah[$index])) {
                    $jumlah = (float) str_replace(',', '.', $request->jumlah[$index]);
                    if ($jumlah <= 0) continue;

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
            }
        });

        return redirect()->route('pengadaan.permintaan.index')->with('success', 'Permintaan ' . ($jenis === 'harian' ? 'harian' : 'catering') . ' berhasil dibuat.');
    }

    public function show(PengadaanBahan $pengadaan)
    {
        $pengadaan->load(['diajukan_oleh_pengguna', 'status_pengadaan', 'detail_pengadaan_bahan.bahan_baku.satuan', 'penerimaan_bahan.detail_penerimaan_bahan']);

        return view('inventory.pengadaan.show', compact('pengadaan'));
    }

    public function pdf(PengadaanBahan $pengadaan)
    {
        $pengadaan->load(['diajukan_oleh_pengguna', 'status_pengadaan', 'detail_pengadaan_bahan.bahan_baku.satuan']);

        $pdf = Pdf::loadView('inventory.pengadaan.pdf', compact('pengadaan'));

        return $pdf->download('Permintaan-Bahan-Baku-' . $pengadaan->nomor_pengadaan . '.pdf');
    }

    public function edit(PengadaanBahan $pengadaan)
    {
        $kode = StatusPengadaan::kodeById($pengadaan->status_pengadaan_id);
        abort_unless(in_array($kode, [StatusPengadaan::DRAFT, StatusPengadaan::MENUNGGU_PEMBELIAN]), 403, 'Permintaan tidak dapat diubah.');

        $pengadaan->load(['detail_pengadaan_bahan.bahan_baku.satuan']);
        $semuaBahan = StokBahan::with(['bahan_baku.satuan'])
            ->where('jenis_persediaan', $pengadaan->jenis_pengadaan)
            ->get();

        return view('inventory.pengadaan.edit', compact('pengadaan', 'semuaBahan'));
    }

    public function update(Request $request, PengadaanBahan $pengadaan)
    {
        $kode = StatusPengadaan::kodeById($pengadaan->status_pengadaan_id);
        abort_unless(in_array($kode, [StatusPengadaan::DRAFT, StatusPengadaan::MENUNGGU_PEMBELIAN]), 403, 'Permintaan tidak dapat diubah.');

        $request->validate([
            'tanggal_pengadaan' => 'required|date',
            'catatan' => 'nullable|string',
            'jumlah' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $pengadaan) {
            $pengadaan->tanggal_pengadaan = $request->tanggal_pengadaan;
            $pengadaan->catatan = $request->catatan;
            $pengadaan->save();

            foreach ($pengadaan->detail_pengadaan_bahan as $detail) {
                $jumlah = (float) ($request->jumlah[$detail->id] ?? $detail->jumlah_dipesan);
                $detail->jumlah_dipesan = max(0, $jumlah);
                $detail->subtotal = $detail->jumlah_dipesan * $detail->harga_satuan;
                $detail->save();
            }
        });

        return redirect()->route('pengadaan.permintaan.show', $pengadaan)->with('success', 'Permintaan berhasil diperbarui.');
    }

    public function cancel(PengadaanBahan $pengadaan)
    {
        $kode = StatusPengadaan::kodeById($pengadaan->status_pengadaan_id);
        abort_unless(in_array($kode, [StatusPengadaan::DRAFT, StatusPengadaan::MENUNGGU_PEMBELIAN, StatusPengadaan::DALAM_PROSES]), 403, 'Permintaan tidak dapat dibatalkan.');

        $pengadaan->status_pengadaan_id = StatusPengadaan::idByKode(StatusPengadaan::DIBATALKAN);
        $pengadaan->save();

        return back()->with('success', 'Permintaan dibatalkan.');
    }

    public function updateStatus(Request $request, $id)
    {
        $pengadaan = PengadaanBahan::findOrFail($id);
        $status = StatusPengadaan::find($request->status);
        if ($status) {
            $pengadaan->status_pengadaan_id = $status->id;
            $pengadaan->save();
        }

        return back()->with('success', 'Status permintaan berhasil diubah.');
    }

    protected function kodePermintaan(): string
    {
        $date = now();
        $count = PengadaanBahan::whereDate('dibuat_pada', $date->toDateString())->count() + 1;

        return 'PRM-' . $date->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
