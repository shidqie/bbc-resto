<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\JadwalPesanan;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
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

        return view('pelanggan.pesanan.catering.create', compact('pakets', 'selectedPaketId'));
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
            $subtotal = $paket->harga_jual * $request->jumlah_porsi;
            $ongkir = 0;
            $ongkir_normal = 0;
            if ($request->metode_pengiriman === 'delivery') {
                $ongkir = \App\Models\Pengiriman::hitungOngkir((float) $request->jarak_km, $request->jumlah_porsi);
                $jarak_km = (float) $request->jarak_km;
                $ongkir_normal = $jarak_km > 0 ? (10000 + ($jarak_km * 3000)) : 0;
            }
            $total = round($subtotal + $ongkir);
            $dp = round($total * 0.5);

            return response()->json([
                'ongkir' => $ongkir,
                'ongkir_normal' => $ongkir_normal,
                'total' => $total,
                'dp' => $dp,
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
            'tanggal_acara' => 'required|date|after_or_equal:' . \Carbon\Carbon::today()->addDays(4)->toDateString(),
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'lokasi_acara' => 'required_if:metode_pengiriman,delivery|nullable|string',
            'paket_id' => 'required|exists:menu,id',
            'jumlah_porsi' => 'required|integer|min:1',
            'komponen' => 'nullable|array',
            'opsi_pembayaran' => 'required|in:dp,lunas',
        ], [
            'nama_pemesan.required' => 'Nama pemesan wajib diisi.',
            'kontak.required' => 'Kontak WhatsApp wajib diisi.',
            'tanggal_acara.required' => 'Tanggal acara wajib diisi.',
            'tanggal_acara.after_or_equal' => 'Harap melakukan pemesanan minimal H-4 sebelum hari pelaksanaan acara.',
            'lokasi_acara.required' => 'Alamat acara wajib diisi.',
            'metode_pengiriman.required' => 'Metode pengiriman wajib dipilih.',
            'jumlah_porsi.required' => 'Jumlah porsi wajib diisi.',
            'jumlah_porsi.min' => 'Jumlah porsi minimal 1.',
        ]);

        $request->merge(['kontak' => WhatsAppNumber::normalize($request->kontak)]);

        $paket = Menu::with('komponen_paket')->findOrFail($request->paket_id);
        $subtotal = $paket->harga_jual * $request->jumlah_porsi;
        
        $ongkir = 0;
        $kalkulasiOngkir = null;
        if ($request->metode_pengiriman === 'delivery') {
            $kalkulasiService = app(\App\Services\KalkulasiPesananService::class);
            $kalkulasiOngkir = $kalkulasiService->hitungOngkir($request->jumlah_porsi, (float) $request->jarak_km);
            $ongkir = $kalkulasiOngkir['biaya_pengiriman'];
        }
        
        $totalTagihan = $subtotal + $ongkir;

        try {
            $pesanan = DB::transaction(function () use ($request, $paket, $subtotal, $totalTagihan, $ongkir, $kalkulasiOngkir) {
            // Normalisasi: data pemesan disimpan di tabel pelanggan, bukan di catatan
            $pelanggan = Auth::guard('pelanggan')->check()
                ? Auth::guard('pelanggan')->user()
                : Pelanggan::firstOrCreate(
                    ['nomor_telepon' => $request->kontak],
                    ['nama' => $request->nama_pemesan, 'alamat' => '-']
                );


            $pesanan = Pesanan::create([
                
                'jenis_pesanan_id' => 2, // Catering
                'pelanggan_id' => $pelanggan->id,
                'status_pesanan_id' => 1, // Menunggu Konfirmasi
                'tanggal_pesanan' => now(),
                'jumlah_sebelum_potongan' => $subtotal,
                'total_tagihan' => $totalTagihan,
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
                'waktu_pengiriman' => $request->jam_pengambilan ?? null,
                'alamat_pengiriman' => $alamat,
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

            // Pengiriman (jika delivery)
            if ($request->metode_pengiriman === 'delivery' && $kalkulasiOngkir) {
                Pengiriman::create([
                    'nomor_pengiriman' => 'DO-' . time(),
                    'pesanan_id' => $pesanan->id,
                    'status_pengiriman_id' => 1, // Menunggu Jadwal
                    'jadwal_pengiriman' => $request->tanggal_acara,
                    'nama_penerima' => $request->nama_pemesan,
                    'nomor_telepon_penerima' => $request->kontak,
                    'alamat_pengiriman' => $request->lokasi_acara,
                    'jarak_pengiriman' => $request->jarak_km,
                    'biaya_pengiriman' => $ongkir,
                    'tarif_per_km' => $kalkulasiOngkir['tarif_per_km'],
                    'jarak_gratis' => $kalkulasiOngkir['jarak_gratis'],
                    'jarak_berbayar' => $kalkulasiOngkir['jarak_berbayar'],
                ]);
            }

            // Buat sesi pembayaran DP 15 menit
            $nominalDp = round($totalTagihan * 0.5); // Catering DP 50%
            Pembayaran::create([
                'kode_pembayaran' => 'PAY-' . strtoupper(uniqid()),
                'pesanan_id' => $pesanan->id,
                'jenis_pembayaran' => 'uang_muka',
                'metode_pembayaran' => null, // Belum dipilih
                'jumlah_dibayar' => $nominalDp,
                'jumlah_tagihan' => $nominalDp,
                'status_verifikasi' => 'belum_dibayar',
                'expires_at' => now()->addMinutes(15),
            ]);

            // Kirim notifikasi ke admin
            $admins = \App\Models\Pengguna::whereHas('peran', function ($q) {
                $q->whereIn('nama_peran', ['Pemilik', 'Admin', 'Manajer', 'Kasir']);
            })->get();
            
            if ($admins->count() > 0) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\PesananBaru(
                    $pesanan, 
                    "Pesanan Katering Baru - Invoice {$pesanan->id_pesanan}", 
                    route('admin.pesanan.catering.index')
                ));
            }

            return $pesanan;
        });

        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('pesanan.bayar', $pesanan->id_pesanan)
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
                $q->where('id_pesanan', 'like', "%{$search}%")
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
            'dibatalkan' => 6,
            default => null,
        };

        if ($statusFilter !== null) {
            $query->where('status_pesanan_id', $statusFilter);
        }

        // ── Filter Status Pembayaran ────────────────────────────────
        $statusPembayaran = $request->status_pembayaran ?? 'all';
        $pembayaranFilter = match ($statusPembayaran) {
            'menunggu_dp' => 1,
            'verifikasi_dp' => 2,
            'menunggu_pelunasan' => 3,
            'verifikasi_lunas' => 4,
            'lunas' => 5,
            default => null,
        };

        if ($pembayaranFilter !== null) {
            $query->where('status_pembayaran_id', $pembayaranFilter);
        }

        // ── Filter Periode ─────────────────────────────────────────
        if ($request->has('periode') && $request->periode != '') {
            $now = \Carbon\Carbon::now();
            switch ($request->periode) {
                case 'hari_ini':
                    $query->whereDate('dibuat_pada', $now->toDateString());
                    break;
                case 'minggu_ini':
                    $query->whereBetween('dibuat_pada', [$now->startOfWeek()->toDateTimeString(), $now->endOfWeek()->toDateTimeString()]);
                    break;
                case 'bulan_ini':
                    $query->whereMonth('dibuat_pada', $now->month)->whereYear('dibuat_pada', $now->year);
                    break;
                case 'kustom':
                    if ($request->has('start_date') && $request->start_date != '') {
                        $query->whereDate('dibuat_pada', '>=', $request->start_date);
                    }
                    if ($request->has('end_date') && $request->end_date != '') {
                        $query->whereDate('dibuat_pada', '<=', $request->end_date);
                    }
                    break;
            }
        }

        $pesanans = $query->paginate(10)->withQueryString();

        $stats = [
            'baru' => Pesanan::where('jenis_pesanan_id', 2)->where('status_pesanan_id', 1)->count(),
            'diproses' => Pesanan::where('jenis_pesanan_id', 2)->whereIn('status_pesanan_id', [2, 3, 4])->count(),
            'selesai' => Pesanan::where('jenis_pesanan_id', 2)->where('status_pesanan_id', 5)->count(),
        ];

        return view('admin.pesanan.catering.index', compact('pesanans', 'stats', 'status'));
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
            'pengiriman',
            'pelanggan',
            'pembayaran',
            'status_pesanan',
            'jenis_pesanan',
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
            return view('admin.pesanan.catering._detail', compact('pesanan', 'kebutuhanBahan'));
        }

        return view('admin.pesanan.catering.show', compact('pesanan', 'kebutuhanBahan'));
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
    public function verifikasiPembayaran(Request $request, $buktiId)
    {
        $pembayaran = Pembayaran::findOrFail($buktiId);
        $pesanan = Pesanan::findOrFail($pembayaran->pesanan_id);

        $request->validate([
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        $pembayaran->update([
            'status_verifikasi' => 'diterima',
            'diverifikasi_oleh' => Auth::id(),
            'tanggal_verifikasi' => now(),
            'catatan_verifikasi' => $request->filled('catatan_admin')
                ? $request->catatan_admin
                : 'Diverifikasi oleh admin',
        ]);

        if ($pembayaran->jenis_pembayaran === 'uang_muka') {
            // Set batas pelunasan H-4 dari tanggal acara
            $batasPelunasan = null;
            if ($pesanan->jadwal_pesanan && $pesanan->jadwal_pesanan->tanggal_acara) {
                $batasPelunasan = \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->subDays(4)->endOfDay();
            }

            $pesanan->update([
                'status_pembayaran_id' => 3, // Menunggu Pelunasan
                'status_pesanan_id' => 2, // Dikonfirmasi
                'batas_pelunasan' => $batasPelunasan
            ]);
        } else {
            $pesanan->update([
                'status_pembayaran_id' => 5, // Lunas
            ]);
        }

        return back()->with('success', 'Bukti pembayaran #'.$pembayaran->kode_pembayaran.' berhasil diverifikasi.');
    }

    /** Export PDF rincian pesanan */
    public function exportPdf($id)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'detail_pesanan.pilihan_pesanan_catering.komponen_paket', 'detail_pesanan.pilihan_pesanan_catering.pilihan_komponen_paket', 'jadwal_pesanan', 'pengiriman'])
            ->findOrFail($id);

        $type = 'catering';
        $kodePesanan = $pesanan->id_pesanan;

        $pdf = Pdf::loadView('pesanan.invoice-pdf', compact('pesanan', 'type', 'kodePesanan'));

        return $pdf->download('bukti-pesanan-'.$pesanan->id_pesanan.'.pdf');
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
            // Jika membatalkan (6) selalu diperbolehkan kecuali sudah selesai
            if ($status != 6 || $currentStatus == 5) {
                return back()->with('error', 'Perubahan status tidak diizinkan dari status saat ini.');
            }
        }

        // Validasi syarat DP (Untuk masuk ke status Dikonfirmasi)
        if ($status === 2 && !in_array($pesanan->status_pembayaran_id, [3, 4, 5])) {
            return back()->with('error', 'Status tidak bisa diubah karena pembayaran DP belum diverifikasi.');
        }

        // Validasi syarat LUNAS (Untuk masuk ke dapur / Sedang Diproses)
        if ($status === 3 && $pesanan->status_pembayaran_id !== 5) {
            return back()->with('error', 'Pesanan hanya bisa masuk ke dapur setelah LUNAS. Verifikasi pelunasan terlebih dahulu.');
        }

        if ($status == 6) {
            $alasan = $request->alasan_batal;
            $pesanan->update([
                'status_pesanan_id' => 6,
                'catatan' => $alasan ? trim($pesanan->catatan.' [BATAL: '.$alasan.']') : $pesanan->catatan,
            ]);

            app(OrderService::class)->restoreStockPesanan($pesanan);

            if ($pesanan->pengiriman) {
                $pesanan->pengiriman->update(['status_pengiriman_id' => 5]); // Gagal / Batal
            }

            return back()->with('success', 'Pesanan dibatalkan.');
        }

        // Jika status berpindah ke Sedang Diproses (ID 3) dan belum pernah dipotong stok
        if ($status == 3 && $pesanan->status_pesanan_id < 3) {
            try {
                app(OrderService::class)->potongStokPesanan($pesanan);
            } catch (\RuntimeException $e) {
                return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage() . ' Silakan tambah stok bahan terlebih dahulu.');
            }
        }
        
        $pesanan->update(['status_pesanan_id' => $status]);

        // Sinkronisasi dengan Pengiriman (Jika ada jadwal pengiriman)
        if ($pesanan->pengiriman) {
            if ($status == 4) { // Siap Dikirim
                $pesanan->pengiriman->update(['status_pengiriman_id' => 2]); // Siap Dikirim di Pengiriman
            } elseif ($status == 5) { // Selesai
                $pesanan->pengiriman->update(['status_pengiriman_id' => 4]); // Selesai/Diterima di Pengiriman
            }
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /** Tolak bukti pembayaran yang diunggah pelanggan */
    public function tolakPembayaran(Request $request, $buktiId)
    {
        $pembayaran = Pembayaran::findOrFail($buktiId);
        $pesanan    = Pesanan::findOrFail($pembayaran->pesanan_id);

        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $pembayaran->update([
            'status_verifikasi'   => 'ditolak',
            'diverifikasi_oleh'   => Auth::id(),
            'tanggal_verifikasi'  => now(),
            'catatan_verifikasi'  => $request->filled('catatan_admin')
                ? $request->catatan_admin
                : 'Bukti ditolak oleh admin',
        ]);

        // Kembalikan status pembayaran ke status sebelumnya
        if ($pembayaran->jenis_pembayaran === 'uang_muka') {
            $pesanan->update(['status_pembayaran_id' => 1]); // Kembali ke Menunggu DP
        } else {
            $pesanan->update(['status_pembayaran_id' => 3]); // Kembali ke Menunggu Pelunasan
        }

        return back()->with('error', 'Bukti pembayaran #'.$pembayaran->kode_pembayaran.' ditolak.');
    }
}
