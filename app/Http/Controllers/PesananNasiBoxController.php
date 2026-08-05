<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPesanan;
use App\Models\JadwalPesanan;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pengantaran;
use App\Models\Pesanan;
use App\Services\OrderService;
use App\Support\WhatsAppNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananNasiBoxController extends Controller
{
    // GET /pesan/nasibox
    public function create(Request $request)
    {
        // 3 = Nasi Box
        $pakets = Menu::where('jenis_menu_id', 3)
            ->where('status_aktif', true)
            ->whereHas('komponen_paket')
            ->get();

        $selectedPaketId = $request->paket_id;

        return view('order.nasi-box.create', compact('pakets', 'selectedPaketId'));
    }

    // POST /pesan/nasibox/preview
    public function preview(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:menu,id',
            'jumlah_box' => 'required|integer|min:1',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'jarak_km' => 'required_if:metode_pengiriman,delivery|nullable|numeric|min:0',
        ]);

        try {
            $paket = Menu::findOrFail($request->paket_id);
            $total = $paket->harga_jual * $request->jumlah_box;
            $ongkir = ($request->metode_pengiriman === 'delivery') ? ($request->jarak_km * 5000) : 0;

            return response()->json([
                'ongkir' => $ongkir,
                'total' => $total + $ongkir,
                'dp' => ($total + $ongkir) * 0.5,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // POST /pesan/nasibox
    public function store(Request $request)
    {
        $request->validate([
            'nama_pemesan' => 'required|string|max:255',
            'kontak' => 'required|string|max:20',
            'tanggal_acara' => 'required|date',
            'lokasi_acara' => 'required|string',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'paket_id' => 'required|exists:menu,id',
            'jumlah_box' => 'required|integer|min:1',
            'opsi_pembayaran' => 'required|in:dp,lunas',
        ]);

        $request->merge(['kontak' => WhatsAppNumber::normalize($request->kontak)]);

        $paket = Menu::findOrFail($request->paket_id);
        $subtotal = $paket->harga_jual * $request->jumlah_box;
        $ongkir = ($request->metode_pengiriman === 'delivery') ? (($request->jarak_km ?? 1) * 5000) : 0;
        $totalTagihan = $subtotal + $ongkir;

        $pesanan = DB::transaction(function () use ($request, $paket, $subtotal, $totalTagihan, $ongkir) {
            $pelanggan = Auth::guard('pelanggan')->check()
                ? Auth::guard('pelanggan')->user()
                : Pelanggan::firstOrCreate(
                    ['nomor_telepon' => $request->kontak],
                    ['nama' => $request->nama_pemesan, 'alamat' => $request->lokasi_acara]
                );

            $pesanan = Pesanan::create([
                'nomor_pesanan' => 'BOX-'.time(),
                'jenis_pesanan_id' => 3, // Nasi Box
                'pelanggan_id' => $pelanggan->id,
                'status_pesanan_id' => 1, // Menunggu Konfirmasi
                'tanggal_pesanan' => now(),
                'jumlah_sebelum_potongan' => $subtotal,
                'total_tagihan' => $totalTagihan,
                'catatan' => $request->catatan,
            ]);

            // Jadwal Pesanan
            JadwalPesanan::create([
                'pesanan_id' => $pesanan->id,
                'tanggal_acara' => $request->tanggal_acara,
                'alamat_pengantaran' => $request->lokasi_acara,
                'nama_penerima' => $request->nama_pemesan,
                'nomor_telepon_penerima' => $request->kontak,
            ]);

            // Detail Pesanan
            DetailPesanan::create([
                'pesanan_id' => $pesanan->id,
                'menu_id' => $paket->id,
                'jumlah' => $request->jumlah_box,
                'harga_satuan' => $paket->harga_jual,
                'subtotal' => $paket->harga_jual * $request->jumlah_box,
            ]);

            if ($request->metode_pengiriman === 'delivery') {
                Pengantaran::create([
                    'nomor_pengantaran' => 'ANT-'.time().'-'.rand(100, 999),
                    'pesanan_id' => $pesanan->id,
                    'status_pengantaran_id' => 1, // Menunggu
                    'jadwal_pengantaran' => $request->tanggal_acara.' 08:00:00',
                    'nama_penerima' => $request->nama_pemesan,
                    'nomor_telepon_penerima' => $request->kontak,
                    'biaya_pengantaran' => $ongkir,
                    'alamat_pengantaran' => $request->lokasi_acara,
                ]);
            }

            return $pesanan;
        });

        return redirect()->route('pesanan.bayar', $pesanan->nomor_pesanan)
            ->with('success', 'Pesanan Nasi Box berhasil dibuat!');
    }

    // ADMIN GET /admin/pesanan/nasibox
    public function index(Request $request)
    {
        $query = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'status_pesanan'])
            ->where('jenis_pesanan_id', 3)
            ->latest();

        if ($request->has('search')) {
            $query->where('nomor_pesanan', 'like', "%{$request->search}%");
        }

        $pesanans = $query->paginate(10);

        $stats = [
            'baru' => Pesanan::where('jenis_pesanan_id', 3)->where('status_pesanan_id', 1)->count(),
            'diproses' => Pesanan::where('jenis_pesanan_id', 3)->whereIn('status_pesanan_id', [2, 3, 4])->count(),
            'selesai' => Pesanan::where('jenis_pesanan_id', 3)->where('status_pesanan_id', 5)->count(),
        ];

        // Karena index view menggunakan $status, fallback.
        $status = 'all';

        return view('order.nasi-box.index', compact('pesanans', 'stats', 'status'));
    }

    public function show($id)
    {
        $pesanan = Pesanan::with([
            'detail_pesanan.menu',
            'jadwal_pesanan',
            'pengantaran',
            'pembayaran',
            'status_pesanan',
        ])->findOrFail($id);

        $kebutuhanBahan = $this->hitungKebutuhanBahan($pesanan);

        return view('order.nasi-box.show', compact('pesanan', 'kebutuhanBahan'));
    }

    public function exportPdf($id)
    {
        $pesanan = Pesanan::with([
            'detail_pesanan.menu',
            'jadwal_pesanan',
            'pengantaran',
            'pembayaran',
            'status_pesanan',
        ])->findOrFail($id);

        $kebutuhanBahan = $this->hitungKebutuhanBahan($pesanan);

        $pdf = Pdf::loadView('order.nasi-box.pdf', compact('pesanan', 'kebutuhanBahan'));

        return $pdf->stream('rincian-nasi-box-'.$pesanan->nomor_pesanan.'.pdf');
    }

    protected function hitungKebutuhanBahan(Pesanan $pesanan): array
    {
        $pesanan->load([
            'detail_pesanan.menu',
            'detail_pesanan.pilihan_pesanan_catering',
        ]);

        $kebutuhanBahanService = app(\App\Services\KebutuhanBahanService::class);
        $agregat = $kebutuhanBahanService->kebutuhanBahanPesanan($pesanan);

        $result = [];
        foreach ($agregat as $item) {
            $bahan = BahanBaku::with('satuan')->find($item['bahan_baku_id']);
            if ($bahan) {
                $result[] = [
                    'nama_bahan' => $bahan->nama_bahan,
                    'total_kebutuhan' => rtrim(rtrim(number_format($item['kebutuhan'], 2, ',', '.'), '0'), ','),
                    'satuan' => $bahan->satuan->singkatan ?? '',
                ];
            }
        }

        return $result;
    }

    public function konfirmasi($id)
    {
        $pesanan = Pesanan::findOrFail($id);

        if ($pesanan->status_pesanan_id == 1) {
            $pesanan->update(['status_pesanan_id' => 2]); // Dikonfirmasi
        }

        return back()->with('success', 'Pesanan berhasil dikonfirmasi.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required']);

        $statusMap = [
            'ditinjau' => 1,
            'menunggu' => 1,
            'terkonfirmasi' => 2,
            'dikonfirmasi' => 2,
            'diproses' => 3,
            'menunggu_pengiriman' => 4,
            'siap' => 4,
            'dikirim' => 5,
            'selesai' => 5,
            'dibatalkan' => 6,
        ];
        $status = $statusMap[$request->status] ?? (int) $request->status;

        $pesanan = Pesanan::findOrFail($id);

        if ($status == 6) {
            $alasan = $request->alasan_batal;
            $pesanan->update([
                'status_pesanan_id' => 6,
                'catatan' => $alasan ? trim($pesanan->catatan.' [BATAL: '.$alasan.']') : $pesanan->catatan,
            ]);

            return back()->with('success', 'Pesanan berhasil dibatalkan.');
        }

        if ($status == 3 && $pesanan->status_pesanan_id < 3) {
            // FR-10: Nasi Box memotong stok saat PRODUKSI DIMULAI.
            app(OrderService::class)->potongStokPesanan($pesanan);
            $pesanan->update(['status_pesanan_id' => 3]);
        } elseif ($status == 5) {
            $pesanan->update(['status_pesanan_id' => 5]);
        } else {
            $pesanan->update(['status_pesanan_id' => $status]);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
