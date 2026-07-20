<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananNasiBoxRequest;
use App\Models\PaketCatering;
use App\Models\PesananNasiBox;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Services\PesananNasiBoxService;
use Illuminate\Http\Request;

class PesananNasiBoxController extends Controller
{
    // ─── PUBLIC ──────────────────────────────────────────────────────────────

    /** GET /pesan/nasi-box */
    public function create()
    {
        $pakets = PaketCatering::with('komponens.opsi.menu')
            ->where('is_active', true)
            ->where('jenis_paket', 'nasi_box')
            ->get();
        return view('pesanan.nasibox.create', compact('pakets'));
    }

    /** POST /pesan/nasi-box/preview (AJAX) */
    public function preview(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:paket_caterings,id',
            'jumlah_box' => 'required|integer|min:10',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'jarak_km' => 'required_if:metode_pengiriman,delivery|nullable|numeric|min:0'
        ]);
        
        try {
            $ongkir = PesananNasiBoxService::hitungOngkir($request->jumlah_box, $request->jarak_km, $request->metode_pengiriman);
            $result = PesananNasiBoxService::hitungTotal($request->paket_id, $request->jumlah_box, $ongkir);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** POST /pesan/nasi-box */
    public function store(StorePesananNasiBoxRequest $request)
    {
        $validated = $request->validated();

        try {
            $ongkir = PesananNasiBoxService::hitungOngkir($validated['jumlah_box'], $validated['jarak_km'] ?? null, $validated['metode_pengiriman']);
            $hitung = PesananNasiBoxService::hitungTotal($validated['paket_id'], $validated['jumlah_box'], $ongkir);
        } catch (\Exception $e) {
            return back()->withErrors(['metode_pengiriman' => $e->getMessage()])->withInput();
        }

        $pesanan = PesananNasiBox::create([
            'kode_pesanan'  => PesananNasiBox::generateKodePesanan(),
            'nama_pemesan'  => $validated['nama_pemesan'],
            'kontak'        => $validated['kontak'],
            'alamat'        => $validated['alamat'],
            'metode_pengiriman' => $validated['metode_pengiriman'],
            'ongkos_kirim'  => $hitung['ongkir'],
            'jarak_km'      => $validated['jarak_km'] ?? null,
            'latitude'      => $validated['latitude'] ?? null,
            'longitude'     => $validated['longitude'] ?? null,
            'tanggal_acara' => $validated['tanggal_acara'],
            'paket_id'      => $validated['paket_id'],
            'jumlah_box'    => $validated['jumlah_box'],
            'total_tagihan' => $hitung['total'],
            'dp_amount'     => $validated['opsi_pembayaran'] === 'lunas' ? $hitung['total'] : $hitung['dp'],
            'status'        => 'menunggu_dp',
            'catatan'       => $validated['catatan'] ?? null,
            'user_id'       => \Illuminate\Support\Facades\Auth::id() ?? null,
        ]);

        foreach ($validated['komponen'] as $komponenId => $menuId) {
            \App\Models\PesananNasiBoxDetail::create([
                'pesanan_nasi_box_id' => $pesanan->id,
                'komponen_paket_id'   => $komponenId,
                'menu_id'             => $menuId
            ]);
        }

        // Generate Midtrans Snap Token
        \App\Http\Controllers\MidtransController::generateSnapToken($pesanan, 'nasi_box');

        return redirect()->route('pesanan.bayar', $pesanan->kode_pesanan)
            ->with('success', 'Pesanan berhasil dibuat! Silakan lanjutkan ke pembayaran DP.');
    }

    // ─── ADMIN ───────────────────────────────────────────────────────────────

    /** GET /admin/pesanan/nasi-box */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = PesananNasiBox::with('paket')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_pesanan', 'like', "%{$search}%")
                  ->orWhere('nama_pemesan', 'like', "%{$search}%");
            });
        }

        $pesanans = $query->paginate(10)->withQueryString();

        $stats = [
            'baru' => PesananNasiBox::whereIn('status', ['menunggu_dp', 'menunggu_konfirmasi'])->count(),
            'diproses' => PesananNasiBox::whereIn('status', ['terkonfirmasi', 'diproses', 'dikirim'])->count(),
            'selesai' => PesananNasiBox::where('status', 'selesai')->count(),
        ];

        return view('admin.pesanan.nasibox.index', compact('pesanans', 'status', 'stats'));
    }

    /** GET /admin/pesanan/nasi-box/{id} */
    public function show(PesananNasiBox $pesanan)
    {
        $pesanan->load(['paket', 'details.komponenPaket', 'details.menu', 'buktiPembayarans']);
        return view('admin.pesanan.nasibox.show', compact('pesanan'));
    }

    /** PATCH /admin/pesanan/nasi-box/{id}/konfirmasi */
    public function konfirmasi(PesananNasiBox $pesanan)
    {
        if ($pesanan->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Pesanan tidak bisa dikonfirmasi dalam status saat ini.');
        }

        $result = PesananNasiBoxService::potongStok($pesanan);

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

    public function updateStatus(Request $request, PesananNasiBox $pesanan)
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
