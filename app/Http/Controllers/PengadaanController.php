<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPenerimaanBahan;
use App\Models\DetailPengadaanBahan;
use App\Models\Pemasok;
use App\Models\PenerimaanBahan;
use App\Models\PengadaanBahan;
use App\Models\Pesanan;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use App\Services\PengadaanService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengadaanController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
    }

    /**
     * Usulan pengadaan otomatis (FR-14).
     * Rumus: kebutuhan produksi + stok pengaman - stok tersedia - sedang dipesan.
     * Dipisah per jenis persediaan (harian / catering).
     */
    public function usulan(Request $request)
    {
        $service = app(PengadaanService::class);
        $hari = (int) $request->get('hari', 7);
        $jenis = $this->validJenis($request->get('jenis', StokBahan::JENIS_HARIAN));

        $usulan = $service->usulanGabungan($hari, $jenis);

        $stats = [
            'bahan_kurang' => $usulan->where('cukup', false)->count(),
            'bahan_cukup' => $usulan->where('cukup', true)->count(),
            'total_usulan' => $usulan->where('cukup', false)->sum('usulan'),
            'sedang_dipesan' => $service->jumlahSedangDipesan($jenis)->sum(),
        ];

        return view('inventory.pengadaan.usulan', compact('usulan', 'stats', 'hari', 'jenis'));
    }

    public function index(Request $request)
    {
        $jenis = $request->get('jenis');

        $query = PengadaanBahan::with(['diajukan_oleh_pengguna', 'status_pengadaan'])->latest();

        if ($jenis && in_array($jenis, [StokBahan::JENIS_HARIAN, StokBahan::JENIS_CATERING], true)) {
            $query->where('jenis_pengadaan', $jenis);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengadaan', 'like', "%{$search}%");
            });
        }

        $pengadaans = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => PengadaanBahan::count(),
            'harian' => PengadaanBahan::harian()->count(),
            'catering' => PengadaanBahan::catering()->count(),
            'total_pengadaan' => PengadaanBahan::sum('total_pengadaan'),
        ];

        return view('inventory.pengadaan.index', compact('pengadaans', 'stats', 'jenis'));
    }

    /**
     * Daftar Pengadaan Harian.
     */
    public function harian(Request $request)
    {
        return $this->index($request->merge(['jenis' => StokBahan::JENIS_HARIAN]));
    }

    /**
     * Daftar Pengadaan Catering.
     */
    public function catering(Request $request)
    {
        return $this->index($request->merge(['jenis' => StokBahan::JENIS_CATERING]));
    }

    public function create(Request $request)
    {
        $jenis = $this->validJenis($request->get('jenis', StokBahan::JENIS_HARIAN));

        $bahanBakus = BahanBaku::with('satuan')->where('status_aktif', true)->orderBy('nama_bahan')->get();
        $pemasoks = Pemasok::where('status_aktif', true)->orderBy('nama_pemasok')->get();

        $prefillItems = [];
        $kodePesananError = null;

        if ($request->has('pesanan_id')) {
            $pesanan = Pesanan::with('detail_pesanan.menu.resep_menu.bahan_baku')->find($request->pesanan_id);
            if ($pesanan) {
                $kebutuhan = [];
                foreach ($pesanan->detail_pesanan as $detail) {
                    if ($detail->menu && $detail->menu->resep_menu) {
                        foreach ($detail->menu->resep_menu as $resep) {
                            $bahanId = $resep->bahan_baku_id;
                            $qty = $resep->jumlah_kebutuhan * $detail->jumlah;
                            $kebutuhan[$bahanId] = ($kebutuhan[$bahanId] ?? 0) + $qty;
                        }
                    }
                }
                foreach ($kebutuhan as $bahanId => $totalKebutuhan) {
                    $bahan = $bahanBakus->firstWhere('id', $bahanId);
                    if ($bahan) {
                        $stokSaatIni = $this->stokBahan($bahanId, $jenis);
                        $kurang = $totalKebutuhan - $stokSaatIni;
                        if ($kurang > 0) {
                            $prefillItems[] = [
                                'bahan_baku_id' => $bahanId,
                                'jumlah_beli' => ceil($kurang * 100) / 100,
                                'keterangan_tambahan' => 'Kebutuhan: '.$totalKebutuhan.' | Stok: '.$stokSaatIni,
                            ];
                        }
                    }
                }
            }
        } else {
            // Bahan yang stoknya (pada jenis ini) di bawah batas minimum.
            $service = app(PengadaanService::class);
            foreach ($service->bahanMenipis($jenis) as $bahan) {
                $stokSaatIni = $this->stokBahan($bahan->id, $jenis);
                $minimal = $this->stokMinimalBahan($bahan->id, $jenis);
                $kurang = $minimal - $stokSaatIni;

                if ($kurang > 0) {
                    $prefillItems[] = [
                        'bahan_baku_id' => $bahan->id,
                        'jumlah_beli' => ceil($kurang * 100) / 100,
                        'keterangan_tambahan' => 'Min: '.$minimal.' | Stok: '.$stokSaatIni,
                    ];
                }
            }
        }

        return view('inventory.pengadaan.create', compact('bahanBakus', 'pemasoks', 'prefillItems', 'kodePesananError', 'jenis'));
    }

    /**
     * Form buat pengadaan harian.
     */
    public function createHarian(Request $request)
    {
        return $this->create($request->merge(['jenis' => StokBahan::JENIS_HARIAN]));
    }

    /**
     * Form buat pengadaan catering.
     */
    public function createCatering(Request $request)
    {
        return $this->create($request->merge(['jenis' => StokBahan::JENIS_CATERING]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pengadaan' => 'required|date',
            'nama_pemasok' => 'required|string|max:150',
            'catatan' => 'nullable|string',
            'jenis_pengadaan' => 'required|in:harian,catering',
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_baku,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $kode = 'PO-'.date('Ymd').'-'.rand(100, 999);

            $pengadaan = PengadaanBahan::create([
                'nomor_pengadaan' => $kode,
                'nama_pemasok' => $request->nama_pemasok,
                'tanggal_pengadaan' => $request->tanggal_pengadaan,
                'catatan' => $request->catatan,
                'diajukan_oleh' => Auth::id(),
                'jenis_pengadaan' => $request->jenis_pengadaan,
                'status_pengadaan_id' => 1, // Menunggu Persetujuan
                'total_pengadaan' => 0,
            ]);

            $totalBiaya = 0;

            foreach ($request->bahan_baku_id as $index => $bahanId) {
                $bahanBaku = BahanBaku::find($bahanId);
                $qty = $request->jumlah[$index];
                $harga = $request->harga_satuan[$index] ?? 0;
                $subtotal = $harga * $qty;

                DetailPengadaanBahan::create([
                    'pengadaan_bahan_id' => $pengadaan->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah_dipesan' => $qty,
                    'jumlah_diterima' => 0,
                    'satuan_id' => $bahanBaku->satuan_id ?? 1,
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                ]);

                $totalBiaya += $subtotal;
            }

            $pengadaan->update(['total_pengadaan' => $totalBiaya]);

            DB::commit();

            return redirect()->route('pengadaan.show', $pengadaan->id)->with('success', "Permintaan Pembelian (PO) {$kode} berhasil dibuat.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat menyimpan pengadaan: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Halaman Penerimaan Bahan — daftar PO yang siap/belum diterima lengkap.
     */
    public function terimaBarang(Request $request)
    {
        $search = $request->input('search', '');
        $jenis = $request->input('jenis', '');

        $query = PengadaanBahan::with(['pemasok', 'status_pengadaan', 'detail_pengadaan_bahan.bahan_baku'])
            ->whereIn('status_pengadaan_id', [2, 5]); // Disetujui atau Diterima Sebagian

        if ($jenis && in_array($jenis, [StokBahan::JENIS_HARIAN, StokBahan::JENIS_CATERING], true)) {
            $query->where('jenis_pengadaan', $jenis);
        }

        if ($search) {
            $query->where('nomor_pengadaan', 'like', "%{$search}%");
        }

        $pengadaans = $query->latest()->paginate(15)->withQueryString();

        return view('inventory.pengadaan.terima-barang', compact('pengadaans', 'search', 'jenis'));
    }

    /**
     * Daftar penerimaan Pengadaan Harian.
     */
    public function terimaBarangHarian(Request $request)
    {
        return $this->terimaBarang($request->merge(['jenis' => StokBahan::JENIS_HARIAN]));
    }

    /**
     * Daftar penerimaan Pengadaan Catering.
     */
    public function terimaBarangCatering(Request $request)
    {
        return $this->terimaBarang($request->merge(['jenis' => StokBahan::JENIS_CATERING]));
    }

    public function show($id)
    {
        $pengadaan = PengadaanBahan::with(['diajukan_oleh_pengguna', 'pemasok', 'detail_pengadaan_bahan.bahan_baku.satuan', 'status_pengadaan'])->findOrFail($id);

        return view('inventory.pengadaan.show', compact('pengadaan'));
    }

    public function exportPdf($id)
    {
        $pengadaan = PengadaanBahan::with(['diajukan_oleh_pengguna', 'pemasok', 'detail_pengadaan_bahan.bahan_baku.satuan', 'status_pengadaan'])
            ->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('inventory.pengadaan.pdf', compact('pengadaan'))
            ->setPaper('a4');

        return $pdf->stream('PO-'.$pengadaan->nomor_pengadaan.'.pdf');
    }

    public function formTerima($id)
    {
        $pengadaan = PengadaanBahan::with(['detail_pengadaan_bahan.bahan_baku.satuan', 'pemasok'])->findOrFail($id);

        if ($pengadaan->status_pengadaan_id == 4) { // SELESAI
            return redirect()->route('pengadaan.index')->with('error', "PO {$pengadaan->nomor_pengadaan} sudah diterima.");
        }

        return view('inventory.pengadaan.terima', compact('pengadaan'));
    }

    public function prosesTerima(Request $request, $id)
    {
        $pengadaan = PengadaanBahan::with('detail_pengadaan_bahan.bahan_baku')->findOrFail($id);

        if ($pengadaan->status_pengadaan_id == 4) { // SELESAI
            return redirect()->route('pengadaan.index')->with('error', 'PO sudah diterima sebelumnya.');
        }

        $request->validate([
            'jumlah_aktual' => 'required|array',
            'jumlah_aktual.*' => 'required|numeric|min:0',
            'harga_aktual' => 'required|array',
            'harga_aktual.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $penerimaan = PenerimaanBahan::create([
                'nomor_penerimaan' => 'RCV-'.date('Ymd').'-'.rand(100, 999),
                'pengadaan_bahan_id' => $pengadaan->id,
                'diterima_pada' => now(),
                'catatan' => $request->catatan,
                'diterima_oleh' => Auth::id(),
            ]);

            $totalBelanja = 0;
            $semuaLengkap = true;

            foreach ($pengadaan->detail_pengadaan_bahan as $detail) {
                $sisa = (float) $detail->jumlah_dipesan - (float) $detail->jumlah_diterima;
                $actualQty = (float) ($request->jumlah_aktual[$detail->id] ?? $detail->jumlah_dipesan);

                // Tidak boleh menerima melebihi sisa pesanan.
                $actualQty = min(max(0, $actualQty), $sisa);

                $actualPrice = (float) ($request->harga_aktual[$detail->id] ?? $detail->harga_satuan);
                $subtotal = $actualQty * $actualPrice;
                $totalBelanja += $subtotal;

                $detailPenerimaan = DetailPenerimaanBahan::create([
                    'penerimaan_bahan_id' => $penerimaan->id,
                    'detail_pengadaan_bahan_id' => $detail->id,
                    'jumlah_diterima' => $actualQty,
                    'harga_satuan' => $actualPrice,
                ]);

                // Update jumlah diterima kumulatif pada detail pengadaan.
                $detail->update([
                    'jumlah_diterima' => (float) $detail->jumlah_diterima + $actualQty,
                    'harga_satuan' => $actualPrice,
                    'subtotal' => $actualPrice * (float) $detail->jumlah_diterima,
                ]);

                // Jenis pengadaan TIDAK boleh diubah di sini (server-side).
                // Stok masuk ke jenis persediaan sesuai jenis pengadaan asal.
                if ($actualQty > 0) {
                    $this->stockService->addStock(
                        $detail->bahan_baku_id,
                        $actualQty,
                        "Penerimaan PO: {$pengadaan->nomor_pengadaan} (".ucfirst($pengadaan->jenis_pengadaan).')',
                        1,
                        Auth::id(),
                        ['detail_penerimaan_bahan_id' => $detailPenerimaan->id],
                        $pengadaan->jenis_pengadaan,
                    );
                }

                // Diterima penuh bila sisa setelah penerimaan <= 0.
                if ((float) $detail->jumlah_diterima + 0.0001 < (float) $detail->jumlah_dipesan) {
                    $semuaLengkap = false;
                }
            }

            $statusId = $semuaLengkap ? 4 : $this->statusDiterimaSebagian();
            $pengadaan->update([
                'status_pengadaan_id' => $statusId,
                'total_pengadaan' => $totalBelanja,
            ]);

            DB::commit();

            return redirect()->route('pengadaan.index')->with('success', "Bahan baku dari PO {$pengadaan->nomor_pengadaan} berhasil diterima. Stok terupdate.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat memproses penerimaan: '.$e->getMessage());
        }
    }

    protected function validJenis($jenis): string
    {
        return in_array($jenis, [StokBahan::JENIS_HARIAN, StokBahan::JENIS_CATERING], true)
            ? $jenis
            : StokBahan::JENIS_HARIAN;
    }

    protected function stokBahan(int $bahanBakuId, string $jenis): float
    {
        return (float) (StokBahan::where('bahan_baku_id', $bahanBakuId)
            ->where('jenis_persediaan', $jenis)->value('jumlah_stok') ?? 0);
    }

    protected function stokMinimalBahan(int $bahanBakuId, string $jenis): float
    {
        return (float) (StokBahan::where('bahan_baku_id', $bahanBakuId)
            ->where('jenis_persediaan', $jenis)->value('stok_minimal') ?? 0);
    }

    protected function statusDiterimaSebagian(): int
    {
        $status = StatusPengadaan::firstOrCreate(
            ['kode_status' => 'DITERIMA_SEBAGIAN'],
            ['nama_status' => 'Diterima Sebagian']
        );

        return (int) $status->id;
    }
}
