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

        return view('pelanggan.pesanan.nasi-box.create', compact('pakets', 'selectedPaketId'));
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
            $subtotal = $paket->harga_jual * $request->jumlah_box;
            
            $ongkir = 0;
            if ($request->metode_pengiriman === 'delivery') {
                $ongkir = \App\Models\Pengantaran::hitungOngkir((float) $request->jarak_km, $request->jumlah_box);
            }
            
            $total = round($subtotal + $ongkir);
            $dp = round($total * 0.5);

            return response()->json([
                'ongkir' => $ongkir,
                'total' => $total,
                'dp' => $dp,
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
            'tanggal_acara' => 'required|date|after_or_equal:' . \Carbon\Carbon::today()->addDays(4)->toDateString(),
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'lokasi_acara' => 'required_if:metode_pengiriman,delivery|nullable|string',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'paket_id' => 'required|exists:menu,id',
            'jumlah_box' => 'required|integer|min:1',
            'opsi_pembayaran' => 'required|in:dp,lunas',
        ], [
            'nama_pemesan.required' => 'Nama pemesan wajib diisi.',
            'kontak.required' => 'Kontak WhatsApp wajib diisi.',
            'tanggal_acara.required' => 'Tanggal acara wajib diisi.',
            'tanggal_acara.after_or_equal' => 'Harap melakukan pemesanan minimal H-4 sebelum hari pelaksanaan acara.',
            'lokasi_acara.required' => 'Alamat acara wajib diisi.',
            'metode_pengiriman.required' => 'Metode pengantaran wajib dipilih.',
            'jumlah_box.required' => 'Jumlah pesanan wajib diisi.',
            'jumlah_box.min' => 'Jumlah pesanan minimal 1.',
        ]);

        $request->merge(['kontak' => WhatsAppNumber::normalize($request->kontak)]);

        $paket = Menu::findOrFail($request->paket_id);
        $subtotal = $paket->harga_jual * $request->jumlah_box;
        
        $ongkir = 0;
        $kalkulasiOngkir = null;
        if ($request->metode_pengiriman === 'delivery') {
            $kalkulasiService = app(\App\Services\KalkulasiPesananService::class);
            $kalkulasiOngkir = $kalkulasiService->hitungOngkir($request->jumlah_box, (float) $request->jarak_km);
            $ongkir = $kalkulasiOngkir['biaya_pengiriman'];
        }
        
        $totalTagihan = $subtotal + $ongkir;

        try {
            $pesanan = DB::transaction(function () use ($request, $paket, $subtotal, $totalTagihan, $ongkir, $kalkulasiOngkir) {
            $pelanggan = Auth::guard('pelanggan')->check()
                ? Auth::guard('pelanggan')->user()
                : Pelanggan::firstOrCreate(
                    ['nomor_telepon' => $request->kontak],
                    ['nama' => $request->nama_pemesan, 'alamat' => '-']
                );

            $pesanan = Pesanan::create([
                
                'jenis_pesanan_id' => 3, // Nasi Box
                'pelanggan_id' => $pelanggan->id,
                'status_pesanan_id' => 1, // Menunggu Konfirmasi
                'tanggal_pesanan' => now(),
                'jumlah_sebelum_potongan' => $subtotal,
                'total_tagihan' => $totalTagihan,
                'catatan' => $request->catatan,
            ]);

            $tanggal_acara_datetime = $request->tanggal_acara;
            if ($request->filled('jam_acara')) {
                $tanggal_acara_datetime = \Carbon\Carbon::parse($request->tanggal_acara . ' ' . $request->jam_acara);
            }
            
            $alamat = $request->metode_pengiriman === 'delivery' ? $request->lokasi_acara : 'Diambil di Toko (Pickup)';

            // Jadwal Pesanan
            JadwalPesanan::create([
                'pesanan_id' => $pesanan->id,
                'tanggal_acara' => $tanggal_acara_datetime,
                'waktu_pengantaran' => $request->jam_pengambilan ?? null,
                'alamat_pengantaran' => $alamat,
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

            // Pengantaran
            if ($request->metode_pengiriman === 'delivery' && $kalkulasiOngkir) {
                Pengantaran::create([
                    'nomor_pengantaran' => 'DO-' . time(),
                    'pesanan_id' => $pesanan->id,
                    'status_pengantaran_id' => 1,
                    'jadwal_pengantaran' => $request->tanggal_acara,
                    'nama_penerima' => $request->nama_pemesan,
                    'nomor_telepon_penerima' => $request->kontak,
                    'alamat_pengantaran' => $request->lokasi_acara,
                    'jarak_pengantaran' => $request->jarak_km,
                    'biaya_pengantaran' => $ongkir,
                    'tarif_per_km' => $kalkulasiOngkir['tarif_per_km'],
                    'jarak_gratis' => $kalkulasiOngkir['jarak_gratis'],
                    'jarak_berbayar' => $kalkulasiOngkir['jarak_berbayar'],
                ]);
            }

            return $pesanan;
        });

        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('pesanan.bayar', $pesanan->id_pesanan)
            ->with('success', 'Pesanan Nasi Box berhasil dibuat!');
    }

    // ADMIN GET /admin/pesanan/nasibox
    public function index(Request $request)
    {
        $query = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'status_pesanan'])
            ->where('jenis_pesanan_id', 3)
            ->latest();

        if ($request->has('search')) {
            $query->where('id_pesanan', 'like', "%{$request->search}%");
        }

        $pesanans = $query->paginate(10);

        $stats = [
            'baru' => Pesanan::where('jenis_pesanan_id', 3)->where('status_pesanan_id', 1)->count(),
            'diproses' => Pesanan::where('jenis_pesanan_id', 3)->whereIn('status_pesanan_id', [2, 3, 4])->count(),
            'selesai' => Pesanan::where('jenis_pesanan_id', 3)->where('status_pesanan_id', 5)->count(),
        ];

        // Karena index view menggunakan $status, fallback.
        $status = 'all';

        return view('admin.pesanan.nasi-box.index', compact('pesanans', 'stats', 'status'));
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

        if (request()->ajax()) {
            return view('admin.pesanan.nasi-box._detail', compact('pesanan', 'kebutuhanBahan'));
        }

        return view('admin.pesanan.nasi-box.show', compact('pesanan', 'kebutuhanBahan'));
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

        return $pdf->stream('rincian-nasi-box-'.$pesanan->id_pesanan.'.pdf');
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
            'menunggu_pembayaran' => 7,
            'terkonfirmasi' => 8,
            'proses_pengadaan' => 9,
            'bahan_diterima' => 10,
            'sedang_produksi' => 11,
            'produksi_selesai' => 12,
            'selesai' => 5,
            'dibatalkan' => 6,
        ];
        $status = $statusMap[$request->status] ?? (int) $request->status;

        $pesanan = Pesanan::findOrFail($id);

        // Peta transisi status yang diizinkan
        $allowedTransitions = [
            1 => [2, 6], // Menunggu Konfirmasi -> Dikonfirmasi / Dibatalkan
            2 => [3, 6], // Dikonfirmasi -> Sedang Diproses / Dibatalkan
            3 => [4],    // Sedang Diproses -> Siap Dikirim
            4 => [5],    // Siap Dikirim -> Selesai
            5 => [],     // Selesai -> final
            6 => [],     // Dibatalkan -> final
        ];

        $currentStatus = $pesanan->status_pesanan_id;

        // Validasi transisi status
        if (!in_array($status, $allowedTransitions[$currentStatus] ?? [])) {
            if ($status != 6 || $currentStatus == 5) {
                return back()->with('error', 'Perubahan status tidak diizinkan dari status saat ini.');
            }
        }

        // Validasi syarat DP (Untuk masuk ke status 2 atau 3)
        // status_pembayaran_id >= 3 berarti DP sudah diverifikasi (Menunggu Pelunasan / Lunas)
        if (in_array($status, [2, 3]) && !in_array($pesanan->status_pembayaran_id, [3, 4, 5])) {
            return back()->with('error', 'Status tidak bisa diubah karena pembayaran DP belum diverifikasi.');
        }

        if ($status == 6) {
            $alasan = $request->alasan_batal;
            $pesanan->update([
                'status_pesanan_id' => 6,
                'catatan' => $alasan ? trim($pesanan->catatan.' [BATAL: '.$alasan.']') : $pesanan->catatan,
            ]);

            app(OrderService::class)->restoreStockPesanan($pesanan);

            if ($pesanan->pengantaran) {
                $pesanan->pengantaran->update(['status_pengantaran_id' => 5]); // Gagal / Batal
            }

            return back()->with('success', 'Pesanan berhasil dibatalkan.');
        }

        // Jika status berpindah ke Sedang Diproses (ID 3) dan belum pernah dipotong stok
        if ($status == 3 && $pesanan->status_pesanan_id < 3) {
            app(OrderService::class)->potongStokPesanan($pesanan);
        }
        
        $pesanan->update(['status_pesanan_id' => $status]);

        // Sinkronisasi dengan Pengantaran (Jika ada jadwal pengantaran)
        if ($pesanan->pengantaran) {
            if ($status == 4) { // Siap Dikirim
                $pesanan->pengantaran->update(['status_pengantaran_id' => 2]); // Siap Dikirim di Pengantaran
            } elseif ($status == 5) { // Selesai
                $pesanan->pengantaran->update(['status_pengantaran_id' => 4]); // Selesai/Diterima di Pengantaran
            }
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
