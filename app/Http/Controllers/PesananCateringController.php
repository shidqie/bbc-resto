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
                'dp_amount'     => $hitung['dp'],
                'status'        => 'menunggu_dp',
                'catatan'       => $validated['catatan'] ?? null,
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

        $query = PesananCatering::with('paket')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($tanggalDari) {
            $query->where('tanggal_acara', '>=', $tanggalDari);
        }
        if ($tanggalSampai) {
            $query->where('tanggal_acara', '<=', $tanggalSampai);
        }

        $pesanans = $query->get();
        return view('admin.pesanan.catering.index', compact('pesanans', 'status'));
    }

    /** GET /admin/pesanan/catering/{id} */
    public function show(PesananCatering $pesanan)
    {
        $pesanan->load(['paket.komponens.opsi.menu', 'details.komponen', 'details.menu', 'addons.layananTambahan', 'buktiPembayarans']);
        return view('admin.pesanan.catering.show', compact('pesanan'));
    }

    /** PATCH /admin/pesanan/catering/{id}/verifikasi-dp */
    public function verifikasiDp(Request $request, $buktiId)
    {
        $bukti = \App\Models\BuktiPembayaran::findOrFail($buktiId);
        $bukti->update(['status' => 'verified', 'catatan_admin' => $request->catatan_admin]);
        $pesanan = $bukti->pesanan;
        if ($pesanan->status === 'menunggu_dp') {
            $pesanan->update(['status' => 'menunggu_konfirmasi']);
        }
        return back()->with('success', 'Bukti DP berhasil diverifikasi.');
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
            return back()->with('success', 'Pesanan dikonfirmasi dan stok telah dipotong.');
        }

        return back()->with('kekurangan_stok', $result)
            ->with('error', 'Stok tidak mencukupi. Silakan buat pengadaan terlebih dahulu.');
    }
}
