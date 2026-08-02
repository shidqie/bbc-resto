<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPenerimaanBahan;
use App\Models\DetailPengadaanBahan;
use App\Models\MutasiStok;
use App\Models\Pemasok;
use App\Models\PenerimaanBahan;
use App\Models\PengadaanBahan;
use App\Models\Pesanan;
use App\Models\StokBahanBaku;
use App\Models\StokCatering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengadaanController extends Controller
{
    public function index(Request $request)
    {
        $query = PengadaanBahan::with(['diajukan_oleh_pengguna', 'status_pengadaan'])->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengadaan', 'like', "%{$search}%");
            });
        }

        $pengadaans = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => PengadaanBahan::count(),
            'total_pengadaan' => PengadaanBahan::sum('total_pengadaan'),
        ];

        return view('inventory.pengadaan.index', compact('pengadaans', 'stats'));
    }

    public function create(Request $request)
    {
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
                        $stokSaatIni = $bahan->stok_bahan_baku?->jumlah_stok ?? 0;
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
        } elseif ($request->get('tipe') === 'harian') {
            // Pengadaan Harian (Reguler)
            // Kumpulkan SEMUA bahan baku yang stok < stok_minimum
            $bahanMenipis = BahanBaku::with('stok_bahan_baku')
                ->where('status_aktif', true)
                ->whereIn('jenis_peruntukan', ['Reguler', 'Semua'])
                ->get()
                ->filter(function ($bahan) {
                    $stokSaatIni = $bahan->stok_bahan_baku?->jumlah_stok ?? 0;

                    return $stokSaatIni < $bahan->stok_minimal;
                });

            foreach ($bahanMenipis as $bahan) {
                $stokSaatIni = $bahan->stok_bahan_baku?->jumlah_stok ?? 0;
                $kurang = $bahan->stok_minimal - $stokSaatIni;

                if ($kurang > 0) {
                    $prefillItems[] = [
                        'bahan_baku_id' => $bahan->id,
                        'jumlah_beli' => ceil($kurang * 100) / 100,
                        'keterangan_tambahan' => 'Min: '.$bahan->stok_minimal.' | Stok: '.$stokSaatIni,
                    ];
                }
            }
        }

        return view('inventory.pengadaan.create', compact('bahanBakus', 'prefillItems', 'kodePesananError'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pengadaan' => 'required|date',
            'nama_pemasok' => 'required|string|max:150',
            'catatan' => 'nullable|string',
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
                'jenis_pengadaan' => $request->get('jenis_pengadaan', 'REGULER'),
                'status_pengadaan_id' => 1, // Menunggu Persetujuan
                'total_pengadaan' => 0,
            ]);

            $totalBiaya = 0;

            foreach ($request->bahan_baku_id as $index => $bahanId) {
                $bahanBaku = BahanBaku::find($bahanId);
                $qty = $request->jumlah[$index];
                $harga = 0; // Harga diisi saat diterima atau jika ada referensi harga
                $subtotal = 0;

                DetailPengadaanBahan::create([
                    'pengadaan_bahan_id' => $pengadaan->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah_dipesan' => $qty,
                    'jumlah_diterima' => 0,
                    'satuan_id' => $bahanBaku->satuan_id ?? 1,
                    'harga_satuan' => $request->harga_satuan[$index] ?? 0,
                    'subtotal' => 0,
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
     * Halaman Penerimaan Bahan — daftar PO yang siap/belum diterima.
     */
    public function terimaBarang(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', ''); // filter opsional

        $query = PengadaanBahan::with(['pemasok', 'status_pengadaan', 'detail_pengadaan_bahan.bahan_baku'])
            ->whereIn('status_pengadaan_id', [2, 3]); // Disetujui (2) & Dikirim (3) — siap terima

        if ($search) {
            $query->where('nomor_pengadaan', 'like', "%{$search}%");
        }

        $pengadaans = $query->latest()->paginate(15)->withQueryString();

        return view('inventory.pengadaan.terima-barang', compact('pengadaans', 'search'));
    }

    public function show($id)
    {
        $pengadaan = PengadaanBahan::with(['diajukan_oleh_pengguna', 'pemasok', 'detail_pengadaan_bahan.bahan_baku.satuan', 'status_pengadaan'])->findOrFail($id);

        return view('inventory.pengadaan.show', compact('pengadaan'));
    }

    public function formTerima($id)
    {
        $pengadaan = PengadaanBahan::with(['detail_pengadaan_bahan.bahan_baku.satuan', 'pemasok'])->findOrFail($id);

        if ($pengadaan->status_pengadaan_id == 3) { // Diterima
            return redirect()->route('pengadaan.index')->with('error', "PO {$pengadaan->nomor_pengadaan} sudah diterima.");
        }

        return view('inventory.pengadaan.terima', compact('pengadaan'));
    }

    public function prosesTerima(Request $request, $id)
    {
        $pengadaan = PengadaanBahan::with('detail_pengadaan_bahan.bahan_baku')->findOrFail($id);

        if ($pengadaan->status_pengadaan_id == 3) {
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

            foreach ($pengadaan->detail_pengadaan_bahan as $detail) {
                $actualQty = $request->jumlah_aktual[$detail->id] ?? $detail->jumlah_dipesan;
                $actualPrice = $request->harga_aktual[$detail->id] ?? 0;
                $subtotal = $actualQty * $actualPrice;
                $totalBelanja += $subtotal;

                DetailPenerimaanBahan::create([
                    'penerimaan_bahan_id' => $penerimaan->id,
                    'detail_pengadaan_bahan_id' => $detail->id,
                    'jumlah_diterima' => $actualQty,
                    'harga_satuan' => $actualPrice,
                ]);

                // Update detail pengadaan
                $detail->update([
                    'harga_satuan' => $actualPrice,
                    'subtotal' => $subtotal,
                ]);

                // Update Stok & Mutasi
                if ($actualQty > 0) {
                    $bahan = $detail->bahan_baku;

                    if ($pengadaan->jenis_pengadaan == 'CATERING') {
                        $stokCatering = StokCatering::firstOrCreate([
                            'pesanan_id' => $pengadaan->pesanan_id,
                            'bahan_baku_id' => $bahan->id,
                        ]);
                        $stokCatering->diterima += $actualQty;
                        $stokCatering->save();
                    } else {
                        $stok = $bahan->stok ?? $bahan->stok_bahan_baku; // if relationship name is 'stok'
                        if ($stok) {
                            $stok->jumlah_stok += $actualQty;
                            $stok->save();
                        } else {
                            StokBahanBaku::create([
                                'bahan_baku_id' => $bahan->id,
                                'jumlah_stok' => $actualQty,
                                'terakhir_diperbarui' => now(),
                            ]);
                        }
                    }

                    MutasiStok::create([
                        'bahan_baku_id' => $bahan->id,
                        'dibuat_oleh' => Auth::id(),
                        'jenis_mutasi_stok_id' => 1, // Masuk
                        'jumlah' => $actualQty,
                        'satuan_id' => $bahan->satuan_id ?? 1,
                        'tanggal_mutasi' => now(),
                        'jenis_stok' => $pengadaan->jenis_pengadaan == 'CATERING' ? 'CATERING' : 'OPERASIONAL',
                        'referensi_id' => $pengadaan->jenis_pengadaan == 'CATERING' ? $pengadaan->pesanan_id : $pengadaan->id,
                        'catatan' => "Penerimaan PO: {$pengadaan->nomor_pengadaan}",
                        'detail_penerimaan_bahan_id' => $penerimaan->id,
                    ]);
                }
            }

            // Update pengadaan
            $pengadaan->update([
                'status_pengadaan_id' => 3, // Diterima
                'total_pengadaan' => $totalBelanja,
            ]);

            DB::commit();

            return redirect()->route('pengadaan.index')->with('success', "Bahan baku dari PO {$pengadaan->nomor_pengadaan} berhasil diterima. Stok terupdate.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat memproses penerimaan: '.$e->getMessage());
        }
    }
}
