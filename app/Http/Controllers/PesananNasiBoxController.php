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
            'menu_id' => 'required|exists:menus,id',
            'jumlah_box' => 'required|integer|min:1',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'jarak_km' => 'required_if:metode_pengiriman,delivery|nullable|numeric|min:0'
        ]);
        
        try {
            $ongkir = PesananNasiBoxService::hitungOngkir($request->jumlah_box, $request->jarak_km, $request->metode_pengiriman);
            $result = PesananNasiBoxService::hitungTotal($request->menu_id, $request->jumlah_box, $ongkir);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** POST /pesan/nasi-box */
    public function store(StorePesananNasiBoxRequest $request)
    {
        $validated = $request->validated();

        // Pastikan menu tersebut merupakan nasi box (berdasarkan KategoriMenu)
        $menu = Menu::with('kategori')->findOrFail($validated['menu_id']);
        if (!$menu->kategori || strtolower($menu->kategori->nama) !== 'nasi box') {
            return back()->withErrors(['menu_id' => 'Varian yang dipilih bukan menu Nasi Box.'])->withInput();
        }

        try {
            $ongkir = PesananNasiBoxService::hitungOngkir($validated['jumlah_box'], $validated['jarak_km'] ?? null, $validated['metode_pengiriman']);
            $hitung = PesananNasiBoxService::hitungTotal($validated['menu_id'], $validated['jumlah_box'], $ongkir);
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
            'menu_id'       => $validated['menu_id'],
            'jumlah_box'    => $validated['jumlah_box'],
            'total_tagihan' => $hitung['total'],
            'dp_amount'     => $hitung['dp'],
            'status'        => 'menunggu_dp',
            'catatan'       => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('pesanan.bayar', $pesanan->kode_pesanan)
            ->with('success', 'Pesanan berhasil dibuat! Silakan lanjutkan ke pembayaran DP.');
    }

    // ─── ADMIN ───────────────────────────────────────────────────────────────

    /** GET /admin/pesanan/nasi-box */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $query = PesananNasiBox::with('menu')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $pesanans = $query->get();
        return view('admin.pesanan.nasibox.index', compact('pesanans', 'status'));
    }

    /** GET /admin/pesanan/nasi-box/{id} */
    public function show(PesananNasiBox $pesanan)
    {
        $pesanan->load(['menu', 'buktiPembayarans']);
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
            return back()->with('success', 'Pesanan dikonfirmasi dan stok telah dipotong.');
        }

        return back()->with('kekurangan_stok', $result)
            ->with('error', 'Stok tidak mencukupi. Silakan buat pengadaan terlebih dahulu.');
    }
}
