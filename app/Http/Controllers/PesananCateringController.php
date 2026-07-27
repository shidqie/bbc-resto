<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananCateringRequest;
use App\Models\PaketCatering;
use App\Models\PesananCatering;
use App\Models\PesananCateringDetail;
use App\Models\LayananTambahan;
use App\Models\KomponenPaket;
use App\Services\PesananCateringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesananCateringController extends Controller
{
    // ─── PUBLIC ──────────────────────────────────────────────────────────────

    /** GET /pesan/catering */
    public function create()
    {
        $pakets = PaketCatering::with('komponens.opsi.menu')
            ->where('is_active', true)
            ->where('jenis_paket', 'catering')
            ->get();
        $layananTambahan = LayananTambahan::all();
        return view('pesanan.catering.create', compact('pakets', 'layananTambahan'));
    }

    /** GET /pesan/catering/komponen/{paketId} (AJAX) */
    public function getKomponen($paketId)
    {
        $paket = PaketCatering::with('komponens.opsi.menu')->findOrFail($paketId);
        return response()->json($paket->komponens->sortBy('urutan')->values());
    }

    /** POST /pesan/catering/preview (AJAX) */
    public function preview(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:paket_caterings,id',
            'jumlah_porsi' => 'required|integer|min:1',
            'layanan_tambahan' => 'nullable|array',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'jarak_km' => 'required_if:metode_pengiriman,delivery|nullable|numeric|min:0'
        ]);
        
        try {
            $ongkir = PesananCateringService::hitungOngkir($request->jumlah_porsi, $request->jarak_km, $request->metode_pengiriman);
            $result = PesananCateringService::hitungTotal(
                $request->paket_id,
                $request->jumlah_porsi,
                $request->layanan_tambahan ?? [],
                $ongkir
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** POST /pesan/catering */
    public function store(StorePesananCateringRequest $request)
    {
        $validated = $request->validated();
        
        try {
            $ongkir = PesananCateringService::hitungOngkir($validated['jumlah_porsi'], $validated['jarak_km'] ?? null, $validated['metode_pengiriman']);
            $hitung = PesananCateringService::hitungTotal(
                $validated['paket_id'],
                $validated['jumlah_porsi'],
                $validated['layanan_tambahan'] ?? [],
                $ongkir
            );
        } catch (\Exception $e) {
            return back()->withErrors(['metode_pengiriman' => $e->getMessage()])->withInput();
        }

        $pesanan = DB::transaction(function () use ($validated, $hitung) {
            $pesanan = PesananCatering::create([
                'kode_pesanan'  => PesananCatering::generateKodePesanan(),
                'nama_pemesan'  => $validated['nama_pemesan'],
                'kontak'        => $validated['kontak'],
                'lokasi_acara'  => $validated['lokasi_acara'],
                'metode_pengiriman' => $validated['metode_pengiriman'],
                'ongkos_kirim'  => $hitung['ongkir'],
                'jarak_km'      => $validated['jarak_km'] ?? null,
                'latitude'      => $validated['latitude'] ?? null,
                'longitude'     => $validated['longitude'] ?? null,
                'tanggal_acara' => $validated['tanggal_acara'],
                'paket_id'      => $validated['paket_id'],
                'jumlah_porsi'  => $validated['jumlah_porsi'],
                'total_tagihan' => $hitung['total'],
                'dp_amount'     => $validated['opsi_pembayaran'] === 'lunas' ? $hitung['total'] : $hitung['dp'],
                'status'        => 'menunggu_dp',
                'catatan'       => $validated['catatan'] ?? null,
                'user_id'       => \Illuminate\Support\Facades\Auth::id() ?? null,
            ]);

            foreach ($validated['komponen'] as $komponenId => $menuId) {
                PesananCateringDetail::create([
                    'pesanan_id'       => $pesanan->id,
                    'komponen_id'      => $komponenId,
                    'menu_id_terpilih' => $menuId,
                ]);
            }

            if (!empty($validated['layanan_tambahan'])) {
                foreach ($validated['layanan_tambahan'] as $layananId) {
                    $pesanan->addons()->create(['layanan_tambahan_id' => $layananId]);
                }
            }

            return $pesanan;
        });

        // Notifikasi Admin
        \App\Models\NotifikasiAdmin::buatNotifikasi(
            'Pesanan Catering Baru #' . $pesanan->kode_pesanan,
            "Pesanan Catering baru dari {$pesanan->nama_pemesan} sejumlah {$pesanan->jumlah_porsi} porsi (Total: Rp " . number_format($pesanan->total_tagihan, 0, ',', '.') . ").",
            'pesanan_baru',
            '/admin/pesanan/catering/' . $pesanan->id
        );

        // Generate Midtrans Snap Token
        \App\Http\Controllers\MidtransController::generateSnapToken($pesanan, 'catering');

        return redirect()->route('pesanan.bayar', $pesanan->kode_pesanan)
            ->with('success', 'Pesanan berhasil dibuat! Silakan lanjutkan ke pembayaran DP.');
    }

    /** GET /pesan/status/{kode} (Publik) */
    public function cekStatus($kode)
    {
        $pesanan = PesananCatering::with(['paket', 'buktiPembayarans'])
            ->where('kode_pesanan', $kode)->firstOrFail();
        return view('pesanan.status', compact('pesanan'), ['type' => 'catering']);
    }

    // ─── ADMIN ───────────────────────────────────────────────────────────────

    /** GET /admin/pesanan/catering */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $tanggalDari = $request->input('tanggal_dari');
        $tanggalSampai = $request->input('tanggal_sampai');
        $search = $request->input('search');

        $query = PesananCatering::with(['paket', 'details.menu'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($tanggalDari) {
            $query->where('tanggal_acara', '>=', $tanggalDari);
        }
        if ($tanggalSampai) {
            $query->where('tanggal_acara', '<=', $tanggalSampai);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_pesanan', 'like', "%{$search}%")
                  ->orWhere('nama_pemesan', 'like', "%{$search}%");
            });
        }

        // Pagination with query string persistence
        $pesanans = $query->paginate(10)->withQueryString();

        $stats = [
            'baru' => PesananCatering::whereIn('status', ['menunggu_dp', 'menunggu_konfirmasi'])->count(),
            'diproses' => PesananCatering::whereIn('status', ['terkonfirmasi', 'diproses', 'dikirim'])->count(),
            'selesai' => PesananCatering::where('status', 'selesai')->count(),
        ];

        return view('admin.pesanan.catering.index', compact('pesanans', 'status', 'stats'));
    }

    /** GET /admin/pesanan/catering/{id} */
    public function show(PesananCatering $pesanan)
    {
        $pesanan->load([
            'paket.komponens.opsi.menu', 
            'details.komponen', 
            'details.menu.resep.bahanBaku.satuan', 
            'addons.layananTambahan', 
            'buktiPembayarans'
        ]);

        // Hitung kebutuhan BOM Bahan Baku khusus pesanan catering ini
        $kebutuhanBahan = [];
        foreach ($pesanan->details as $detail) {
            if ($detail->menu && $detail->menu->resep) {
                foreach ($detail->menu->resep as $resep) {
                    $bahanId = $resep->bahan_baku_id;
                    $qty = $resep->jumlah_kebutuhan * $pesanan->jumlah_porsi;
                    if (!isset($kebutuhanBahan[$bahanId])) {
                        $kebutuhanBahan[$bahanId] = [
                            'bahan_id' => $bahanId,
                            'nama_bahan' => $resep->bahanBaku->nama_bahan ?? 'Bahan Baku',
                            'satuan' => $resep->bahanBaku->satuan->nama_satuan ?? '',
                            'stok_sekarang' => $resep->bahanBaku->stok ?? 0,
                            'total_kebutuhan' => 0,
                        ];
                    }
                    $kebutuhanBahan[$bahanId]['total_kebutuhan'] += $qty;
                }
            }
        }

        return view('admin.pesanan.catering.show', compact('pesanan', 'kebutuhanBahan'));
    }

    public function verifikasiDp(Request $request, $buktiId)
    {
        $bukti = \App\Models\BuktiPembayaran::findOrFail($buktiId);
        $catatan = $request->input('catatan_admin', 'Diverifikasi oleh Admin');
        $bukti->update(['status' => 'verified', 'catatan_admin' => $catatan]);
        
        $pesanan = $bukti->pesanan;
        if ($pesanan) {
            if ($bukti->jenis_pembayaran === 'pelunasan') {
                $pesanan->update(['status' => 'lunas']);
                $msg = 'Bukti Pelunasan berhasil diverifikasi. Status pesanan kini LUNAS dan siap diproduksi / diantar!';
            } else {
                $pesanan->update(['status' => 'terkonfirmasi']);
                $msg = 'Bukti DP berhasil diverifikasi. Status pesanan Terkonfirmasi & menunggu pelunasan.';
            }
        } else {
            $msg = 'Bukti pembayaran berhasil diverifikasi.';
        }
        
        return back()->with('success', $msg);
    }

    /** PATCH /admin/pesanan/catering/{id}/konfirmasi */
    public function konfirmasi(PesananCatering $pesanan)
    {
        if ($pesanan->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Pesanan tidak bisa dikonfirmasi dalam status saat ini.');
        }

        $result = PesananCateringService::potongStok($pesanan);

        if ($result === true) {
            $pesanan->update(['status' => 'terkonfirmasi']);
            // Kirim Email Konfirmasi Pembayaran
            if ($pesanan->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($pesanan->email)->send(new \App\Mail\PaymentReceiptMail($pesanan));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal mengirim email konfirmasi: ' . $e->getMessage());
                }
            }
            return back()->with('success', 'Pesanan dikonfirmasi dan stok telah dipotong.');
        }

        return back()->with('kekurangan_stok', $result)
            ->with('error', 'Stok tidak mencukupi. Silakan buat pengadaan terlebih dahulu.');
    }

    public function updateStatus(Request $request, PesananCatering $pesanan)
    {
        $request->validate([
            'status' => 'required|in:terkonfirmasi,diproses,dikirim,selesai,lunas,dibatalkan',
            'alasan_batal' => 'nullable|string'
        ]);

        $pesanan->status = $request->status;
        if ($request->status === 'dibatalkan') {
            $pesanan->alasan_batal = $request->alasan_batal;
        }
        $pesanan->save();

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
