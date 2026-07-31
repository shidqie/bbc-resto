<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananCateringRequest;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\JadwalPesanan;
use App\Models\Pengantaran;
use App\Services\PesananCateringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PesananCateringController extends Controller
{
    // GET /pesan/catering
    public function create()
    {
        // 2 = Catering
        $pakets = Menu::where('jenis_menu_id', 2)
            ->where('status_aktif', true)
            ->get();
            
        return view('order.catering.create', compact('pakets'));
    }

    // POST /pesan/catering/preview
    public function preview(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:menu,id',
            'jumlah_porsi' => 'required|integer|min:1',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'jarak_km' => 'required_if:metode_pengiriman,delivery|nullable|numeric|min:0'
        ]);
        
        try {
            $paket = Menu::findOrFail($request->paket_id);
            $total = $paket->harga_jual * $request->jumlah_porsi;
            $ongkir = ($request->metode_pengiriman === 'delivery') ? ($request->jarak_km * 5000) : 0;
            
            return response()->json([
                'ongkir' => $ongkir,
                'total' => $total + $ongkir,
                'dp' => ($total + $ongkir) * 0.5
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // POST /pesan/catering
    public function store(Request $request)
    {
        $request->validate([
            'nama_pemesan' => 'required|string|max:255',
            'kontak' => 'required|string|max:50',
            'tanggal_acara' => 'required|date',
            'lokasi_acara' => 'required|string',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'paket_id' => 'required|exists:menu,id',
            'jumlah_porsi' => 'required|integer|min:1',
            'opsi_pembayaran' => 'required|in:dp,lunas'
        ]);
        
        $paket = Menu::findOrFail($request->paket_id);
        $subtotal = $paket->harga_jual * $request->jumlah_porsi;
        $ongkir = ($request->metode_pengiriman === 'delivery') ? (($request->jarak_km ?? 1) * 5000) : 0;
        $totalTagihan = $subtotal + $ongkir;

        $pesanan = DB::transaction(function () use ($request, $paket, $totalTagihan) {
            $pesanan = Pesanan::create([
                'nomor_pesanan' => 'CAT-' . time(),
                'jenis_pesanan_id' => 2, // Catering
                'status_pesanan_id' => 1, // Menunggu Konfirmasi
                'total_tagihan' => $totalTagihan,
                'catatan' => $request->catatan,
            ]);

            // Jadwal Pesanan
            JadwalPesanan::create([
                'pesanan_id' => $pesanan->id,
                'tanggal_acara' => $request->tanggal_acara,
                'lokasi_acara' => $request->lokasi_acara,
            ]);

            // Detail Pesanan
            DetailPesanan::create([
                'pesanan_id' => $pesanan->id,
                'menu_id' => $paket->id,
                'jumlah' => $request->jumlah_porsi,
                'harga_satuan' => $paket->harga_jual,
                'subtotal' => $paket->harga_jual * $request->jumlah_porsi
            ]);

            if ($request->metode_pengiriman === 'delivery') {
                Pengantaran::create([
                    'pesanan_id' => $pesanan->id,
                    'status_pengantaran_id' => 1, // Menunggu
                    'biaya_pengantaran' => $ongkir,
                    'jarak_km' => $request->jarak_km ?? null,
                    'alamat_pengantaran' => $request->lokasi_acara
                ]);
            }

            return $pesanan;
        });

        return redirect()->route('pesanan.bayar', $pesanan->nomor_pesanan)
            ->with('success', 'Pesanan Catering berhasil dibuat!');
    }

    // ADMIN GET /admin/pesanan/catering
    public function index(Request $request)
    {
        $query = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'status_pesanan'])
                        ->where('jenis_pesanan_id', 2)
                        ->latest();

        if ($request->has('search')) {
            $query->where('nomor_pesanan', 'like', "%{$request->search}%");
        }

        $pesanans = $query->paginate(10);

        $stats = [
            'baru' => Pesanan::where('jenis_pesanan_id', 2)->where('status_pesanan_id', 1)->count(),
            'diproses' => Pesanan::where('jenis_pesanan_id', 2)->whereIn('status_pesanan_id', [2,3,4])->count(),
            'selesai' => Pesanan::where('jenis_pesanan_id', 2)->where('status_pesanan_id', 5)->count(),
        ];

        // Karena index view menggunakan $status, fallback.
        $status = 'all';

        return view('order.catering.index', compact('pesanans', 'stats', 'status'));
    }

    public function show($id)
    {
        $pesanan = Pesanan::with([
            'detail_pesanan.menu',
            'jadwal_pesanan',
            'pengantaran',
            'pembayaran',
            'status_pesanan'
        ])->findOrFail($id);

        $kebutuhanBahan = [];
        return view('order.catering.show', compact('pesanan', 'kebutuhanBahan'));
    }

    public function updateStatus(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $request->validate(['status' => 'required|integer']);

        if ($request->status == 5 && $pesanan->status_pesanan_id != 5) {
            app(\App\Services\OrderService::class)->completeOrder($pesanan);
        }

        $pesanan->update(['status_pesanan_id' => $request->status]);
        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
