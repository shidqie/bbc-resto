<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\JadwalPesanan;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pengantaran;
use App\Models\Pesanan;
use App\Services\OrderService;
use App\Support\WhatsAppNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananCateringController extends Controller
{
    // GET /pesan/catering
    public function create(Request $request)
    {
        // 2 = Catering
        $pakets = Menu::where('jenis_menu_id', 2)
            ->where('status_aktif', true)
            ->whereHas('komponen_paket')
            ->get();

        $selectedPaketId = $request->paket_id;

        return view('order.catering.create', compact('pakets', 'selectedPaketId'));
    }

    // GET /pesan/catering/komponen/{paketId}
    public function getKomponen($paketId)
    {
        $paket = Menu::with('komponen_paket.opsi')->findOrFail($paketId);

        return response()->json(
            $paket->komponen_paket->map(fn ($k) => [
                'id' => $k->id,
                'nama_komponen' => $k->nama_komponen,
                'tipe' => $k->tipe_komponen === 'tetap' ? 'fixed' : 'choice',
                'opsi' => $k->opsi->map(fn ($o) => [
                    'menu' => [
                        'id' => $o->id,
                        'nama' => $o->nama_pilihan,
                        'foto' => null,
                    ],
                ]),
            ])
        );
    }

    // POST /pesan/catering/preview
    public function preview(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:menu,id',
            'jumlah_porsi' => 'required|integer|min:1',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'jarak_km' => 'required_if:metode_pengiriman,delivery|nullable|numeric|min:0',
        ]);

        try {
            $paket = Menu::findOrFail($request->paket_id);
            $total = $paket->harga_jual * $request->jumlah_porsi;
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

    // POST /pesan/catering
    public function store(Request $request)
    {
        $request->validate([
            'nama_pemesan' => 'required|string|max:255',
            'kontak' => 'required|string|max:20',
            'tanggal_acara' => 'required|date',
            'lokasi_acara' => 'required|string',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'paket_id' => 'required|exists:menu,id',
            'jumlah_porsi' => 'required|integer|min:1',
            'komponen' => 'nullable|array',
            'opsi_pembayaran' => 'required|in:dp,lunas',
        ]);

        $request->merge(['kontak' => WhatsAppNumber::normalize($request->kontak)]);

        $paket = Menu::with('komponen_paket')->findOrFail($request->paket_id);
        $subtotal = $paket->harga_jual * $request->jumlah_porsi;
        $ongkir = ($request->metode_pengiriman === 'delivery') ? (($request->jarak_km ?? 1) * 5000) : 0;
        $totalTagihan = $subtotal + $ongkir;

        try {
            $pesanan = DB::transaction(function () use ($request, $paket, $subtotal, $totalTagihan, $ongkir) {
            // Normalisasi: data pemesan disimpan di tabel pelanggan, bukan di catatan
            $pelanggan = Auth::guard('pelanggan')->check()
                ? Auth::guard('pelanggan')->user()
                : Pelanggan::firstOrCreate(
                    ['nomor_telepon' => $request->kontak],
                    ['nama' => $request->nama_pemesan, 'alamat' => $request->lokasi_acara]
                );

            // Kode invoice berurutan per hari: CTR-20260801-001
            $prefix = 'CTR-'.now()->format('Ymd');
            $seq = Pesanan::where('nomor_pesanan', 'like', $prefix.'-%')->count();
            do {
                $seq++;
                $nomorPesanan = sprintf('%s-%03d', $prefix, $seq);
            } while (Pesanan::where('nomor_pesanan', $nomorPesanan)->exists());

            $pesanan = Pesanan::create([
                'nomor_pesanan' => $nomorPesanan,
                'jenis_pesanan_id' => 2, // Catering
                'pelanggan_id' => $pelanggan->id,
                'status_pesanan_id' => 1, // Menunggu Konfirmasi
                'tanggal_pesanan' => now(),
                'jumlah_sebelum_potongan' => $subtotal,
                'total_tagihan' => $totalTagihan,
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
            $detail = DetailPesanan::create([
                'pesanan_id' => $pesanan->id,
                'menu_id' => $paket->id,
                'jumlah' => $request->jumlah_porsi,
                'harga_satuan' => $paket->harga_jual,
                'subtotal' => $paket->harga_jual * $request->jumlah_porsi,
            ]);

            // Simpan pilihan komponen (komponen[kategori_id] = pilihan_id)
            if ($request->filled('komponen')) {
                foreach ($request->komponen as $komponenPaketId => $pilihanId) {
                    DB::table('pilihan_pesanan_catering')->insert([
                        'detail_pesanan_id' => $detail->id,
                        'item_paket_id' => $komponenPaketId,
                        'pilihan_item_paket_id' => $pilihanId,
                    ]);
                }
            }

            if ($request->metode_pengiriman === 'delivery') {
                Pengantaran::create([
                    'nomor_pengantaran' => 'ANT-'.time().'-'.rand(100, 999),
                    'pesanan_id' => $pesanan->id,
                    'status_pengantaran_id' => 1, // Menunggu
                    'jadwal_pengantaran' => $request->tanggal_acara.' 08:00:00',
                    'nama_penerima' => $request->nama_pemesan,
                    'nomor_telepon_penerima' => $request->kontak,
                    'alamat_pengantaran' => $request->lokasi_acara,
                    'biaya_pengantaran' => $ongkir,
                ]);
            }

            // Deduct stok bahan baku
            $kebutuhanService = app(\App\Services\KebutuhanBahanService::class);
            $pesanan->load('detail_pesanan.menu');
            $stokCukup = $kebutuhanService->deductBahanPesanan($pesanan, 'catering');

            if (!$stokCukup) {
                throw new \Exception('StokBahanTidakCukup');
            }

            return $pesanan;
        });

        } catch (\Exception $e) {
            if ($e->getMessage() === 'StokBahanTidakCukup') {
                return redirect()->back()->with('error', 'Pesanan gagal diproses karena stok bahan baku catering tidak mencukupi.');
            }
            throw $e;
        }

        return redirect()->route('pesanan.bayar', $pesanan->nomor_pesanan)
            ->with('success', 'Pesanan Catering berhasil dibuat!');
    }

    // ADMIN GET /admin/pesanan/catering
    public function index(Request $request)
    {
        $status = $request->status ?? 'all';

        $query = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'status_pesanan', 'pembayaran', 'pelanggan'])
            ->where('jenis_pesanan_id', 2)
            ->latest();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pesanan', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($p) use ($search) {
                        $p->where('nama', 'like', "%{$search}%")
                            ->orWhere('nomor_telepon', 'like', "%{$search}%");
                    })
                    ->orWhereHas('jadwal_pesanan', function ($j) use ($search) {
                        $j->where('nama_penerima', 'like', "%{$search}%")
                            ->orWhere('nomor_telepon_penerima', 'like', "%{$search}%");
                    });
            });
        }

        $statusFilter = match ($status) {
            'ditinjau' => 1,
            'terkonfirmasi' => 2,
            'diproses' => 3,
            'selesai' => 5,
            default => null,
        };

        if ($statusFilter !== null) {
            $query->where('status_pesanan_id', $statusFilter);
        }

        $pesanans = $query->paginate(10);

        $stats = [
            'baru' => Pesanan::where('jenis_pesanan_id', 2)->where('status_pesanan_id', 1)->count(),
            'diproses' => Pesanan::where('jenis_pesanan_id', 2)->whereIn('status_pesanan_id', [2, 3, 4])->count(),
            'selesai' => Pesanan::where('jenis_pesanan_id', 2)->where('status_pesanan_id', 5)->count(),
        ];

        return view('order.catering.index', compact('pesanans', 'stats', 'status'));
    }

    public function show(Request $request, $id)
    {
        $pesanan = Pesanan::with([
            'detail_pesanan.menu',
            'detail_pesanan.menu.resep_menu.bahan_baku.satuan',
            'detail_pesanan.menu.resep_menu.satuan',
            'detail_pesanan.pilihan_pesanan_catering.komponen_paket',
            'detail_pesanan.pilihan_pesanan_catering.pilihan_komponen_paket',
            'jadwal_pesanan',
            'pengantaran',
            'pelanggan',
            'pembayaran.status_pembayaran',
            'pembayaran.jenis_pembayaran',
            'status_pesanan',
        ])->findOrFail($id);

        // Kebutuhan bahan baku (BOM) untuk acara ini (FR-08)
        $kebutuhanBahanService = app(\App\Services\KebutuhanBahanService::class);
        $agregat = $kebutuhanBahanService->kebutuhanBahanPesanan($pesanan);

        $kebutuhan = [];
        foreach ($agregat as $item) {
            $bahan = \App\Models\BahanBaku::with('stok_relasi', 'satuan')->find($item['bahan_baku_id']);
            if (! $bahan) {
                continue;
            }
            $kebutuhan[$bahan->id] = [
                'nama_bahan' => $bahan->nama_bahan,
                'satuan' => ($bahan->satuan->nama_satuan ?? '-'),
                'stok_sekarang' => (float) ($bahan->stok_relasi->jumlah_stok ?? 0),
                'total_kebutuhan' => $item['kebutuhan'],
            ];
        }
        $kebutuhanBahan = array_values($kebutuhan);

        if ($request->ajax()) {
            return view('order.catering._detail', compact('pesanan', 'kebutuhanBahan'));
        }

        return view('order.catering.show', compact('pesanan', 'kebutuhanBahan'));
    }

    /** Konfirmasi pesanan setelah DP terverifikasi (status 1 → 2) */
    public function konfirmasi(Request $request, $id)
    {
        $pesanan = Pesanan::with('pembayaran')->findOrFail($id);

        if ($pesanan->status_pesanan_id != 1) {
            return back()->with('error', 'Pesanan ini tidak dalam status menunggu konfirmasi.');
        }

        $total = (float) $pesanan->total_tagihan;
        $dpBayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');

        if ($lunas < $total * 0.5 && $dpBayar < $total * 0.5) {
            return back()->with('error', 'Uang muka (50%) belum terbayar & terverifikasi. Verifikasi bukti pembayaran terlebih dahulu.');
        }

        $pesanan->update(['status_pesanan_id' => 2]);

        return back()->with('success', 'Pesanan berhasil dikonfirmasi.');
    }

    /** Verifikasi bukti pembayaran yang diunggah pelanggan */
    public function verifikasiDp(Request $request, $buktiId)
    {
        $pembayaran = Pembayaran::findOrFail($buktiId);

        $request->validate([
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        $pembayaran->update([
            'status_pembayaran_id' => 3, // LUNAS
            'diproses_oleh' => Auth::id(),
            'catatan' => $request->filled('catatan_admin')
                ? $request->catatan_admin
                : 'Diverifikasi oleh admin',
        ]);

        return back()->with('success', 'Bukti pembayaran #'.$pembayaran->nomor_pembayaran.' berhasil diverifikasi.');
    }

    /** Export PDF rincian pesanan */
    public function exportPdf($id)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'detail_pesanan.pilihan_pesanan_catering.komponen_paket', 'detail_pesanan.pilihan_pesanan_catering.pilihan_komponen_paket', 'jadwal_pesanan', 'pengantaran'])
            ->findOrFail($id);

        $type = 'catering';
        $kodePesanan = $pesanan->nomor_pesanan;

        $pdf = Pdf::loadView('pesanan.invoice-pdf', compact('pesanan', 'type', 'kodePesanan'));

        return $pdf->download('bukti-pesanan-'.$pesanan->nomor_pesanan.'.pdf');
    }

    public function updateStatus(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);
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

        if ($status == 6) {
            $alasan = $request->alasan_batal;
            $pesanan->update([
                'status_pesanan_id' => 6,
                'catatan' => $alasan ? trim($pesanan->catatan.' [BATAL: '.$alasan.']') : $pesanan->catatan,
            ]);

            return back()->with('success', 'Pesanan dibatalkan.');
        }

        // Jika status berpindah ke Sedang Produksi (ID 11) dan belum pernah dipotong stok
        if ($status == 11 && $pesanan->status_pesanan_id < 11) {
            app(OrderService::class)->potongStokPesanan($pesanan);
        }
        
        $pesanan->update(['status_pesanan_id' => $status]);

        // Jika metode pengiriman = diantar dan status = Produksi Selesai
        if ($status == 12 && $pesanan->metode_pengiriman === 'diantar') {
            // Cek apakah sudah ada di pengantaran
            if (!$pesanan->pengantaran) {
                // Buat data pengantaran dengan status Menunggu (ID 1)
                $pesanan->pengantaran()->create([
                    'nomor_pengantaran' => 'DO-' . time() . '-' . $pesanan->id,
                    'status_pengantaran_id' => 1,
                    'jadwal_pengantaran' => $pesanan->jadwal_pesanan ? $pesanan->jadwal_pesanan->tanggal_acara . ' ' . ($pesanan->jadwal_pesanan->waktu_pengantaran ?? '00:00:00') : now(),
                    'nama_penerima' => $pesanan->jadwal_pesanan ? $pesanan->jadwal_pesanan->nama_penerima : $pesanan->pelanggan->nama ?? 'Unknown',
                    'nomor_telepon_penerima' => $pesanan->jadwal_pesanan ? $pesanan->jadwal_pesanan->nomor_telepon_penerima : $pesanan->pelanggan->telepon ?? '000',
                    'alamat_pengantaran' => $pesanan->jadwal_pesanan ? $pesanan->jadwal_pesanan->alamat_pengantaran : 'Alamat belum diatur',
                ]);
            }
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
