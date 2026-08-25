<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPesanan;
use App\Models\JadwalPesanan;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Services\OrderService;
use App\Services\NasiBoxCapacityService;
use App\Support\WhatsAppNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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

        $komponenMap = [];
        $cateringCtrl = new \App\Http\Controllers\PesananCateringController();
        foreach ($pakets as $p) {
            $komponenMap[$p->id] = $cateringCtrl->getKomponen($p->id)->getData();
        }

        return view('pelanggan.pesanan.nasi-box.create', compact('pakets', 'selectedPaketId', 'komponenMap'));
    }

    // GET /pesan/nasi-box/komponen/{paketId}
    public function getKomponen($paketId)
    {
        return (new \App\Http\Controllers\PesananCateringController())->getKomponen($paketId);
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
                $ongkir = \App\Models\Pengiriman::hitungOngkir((float) $request->jarak_km, $request->jumlah_box);
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

    // GET /pesan/nasi-box/cek-kapasitas
    public function checkCapacity(Request $request, NasiBoxCapacityService $capacityService)
    {
        $request->validate([
            'tanggal_acara' => 'required|date',
            'jumlah_box' => 'required|integer|min:1'
        ]);

        $hasil = $capacityService->cekKetersediaan($request->tanggal_acara, $request->jumlah_box);
        
        return response()->json($hasil);
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
            'metode_pengiriman.required' => 'Metode pengiriman wajib dipilih.',
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
            $lockKey = 'nasi_box_capacity_' . $request->tanggal_acara;
            
            // Gunakan Cache lock agar tidak ada bentrok pesanan di tanggal yang sama
            $pesanan = Cache::lock($lockKey, 10)->block(5, function () use ($request, $paket, $subtotal, $totalTagihan, $ongkir, $kalkulasiOngkir) {
                return DB::transaction(function () use ($request, $paket, $subtotal, $totalTagihan, $ongkir, $kalkulasiOngkir) {
                    
                    // Verifikasi kapasitas lagi di dalam lock
                    $capacityService = app(\App\Services\NasiBoxCapacityService::class);
                    $cekKapasitas = $capacityService->cekKetersediaan($request->tanggal_acara, $request->jumlah_box);

                    if (!$cekKapasitas['tersedia']) {
                        throw new \Exception($cekKapasitas['pesan']);
                    }

                    $pelanggan = Auth::guard('pelanggan')->check()
                        ? Auth::guard('pelanggan')->user()
                        : Pelanggan::firstOrCreate(
                            ['nomor_telepon' => $request->kontak],
                            ['nama' => $request->nama_pemesan, 'alamat' => '-']
                        );

                    $pesananBaru = Pesanan::create([
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
                        'pesanan_id' => $pesananBaru->id,
                        'tanggal_acara' => $tanggal_acara_datetime,
                        'waktu_pengiriman' => $request->jam_pengambilan ?? null,
                        'alamat_pengiriman' => $alamat,
                        'nama_penerima' => $request->nama_pemesan,
                        'nomor_telepon_penerima' => $request->kontak,
                    ]);

                    // Detail Pesanan
                    DetailPesanan::create([
                        'pesanan_id' => $pesananBaru->id,
                        'menu_id' => $paket->id,
                        'jumlah' => $request->jumlah_box,
                        'harga_satuan' => $paket->harga_jual,
                        'subtotal' => $paket->harga_jual * $request->jumlah_box,
                    ]);

                    // Pengiriman
                    if ($request->metode_pengiriman === 'delivery' && $kalkulasiOngkir) {
                        Pengiriman::create([
                            'nomor_pengiriman' => 'DO-' . time(),
                            'pesanan_id' => $pesananBaru->id,
                            'status_pengiriman_id' => 1,
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

                    // Buat sesi pembayaran awal 12 jam (Lunas 100% atau DP 25%)
                    $isLunasOpsi = $request->opsi_pembayaran === 'lunas';
                    $nominalBayarAwal = $isLunasOpsi ? $totalTagihan : round($totalTagihan * 0.25);
                    $jenisBayarAwal = $isLunasOpsi ? 'pelunasan' : 'uang_muka';

                    Pembayaran::create([
                        'kode_pembayaran' => 'PAY-' . strtoupper(uniqid()),
                        'pesanan_id' => $pesananBaru->id,
                        'jenis_pembayaran' => $jenisBayarAwal,
                        'metode_pembayaran' => null, // Belum dipilih
                        'jumlah_dibayar' => $nominalBayarAwal,
                        'jumlah_tagihan' => $nominalBayarAwal,
                        'status_verifikasi' => 'belum_dibayar',
                        'expires_at' => now()->addHours(12),
                    ]);

                    // Kirim notifikasi
                    $admins = \App\Models\Pengguna::whereHas('peran', function ($q) {
                        $q->whereIn('nama_peran', ['Pemilik', 'Admin', 'Manajer', 'Dapur']);
                    })->get();
                    
                    if ($admins->count() > 0) {
                        $qty = $pesananBaru->detail_pesanan->sum('jumlah') ?: $pesananBaru->detail_pesanan->sum('kuantitas');
                        $namaPemesan = $pelanggan->nama ?? ($pesananBaru->pelanggan->nama ?? null);
                        if (!$namaPemesan && !empty($pesananBaru->catatan)) {
                            if (preg_match('/^Pemesan:\s*(.+)$/m', $pesananBaru->catatan, $m)) {
                                $namaPemesan = trim($m[1]);
                            } else {
                                $namaPemesan = trim(explode('|', $pesananBaru->catatan)[0]);
                            }
                        }
                        $atasNama = $namaPemesan ? " atas nama {$namaPemesan}" : "";
                        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\PesananBaru(
                            $pesananBaru,
                            "Pesanan Baru",
                            "Pesanan Nasi Box #{$pesananBaru->id_pesanan} sebanyak {$qty} box{$atasNama} telah masuk dan menunggu konfirmasi.", 
                            route('admin.pesanan.nasibox.index')
                        ));
                    }

                    return $pesananBaru;
                });
            });

        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return back()->withInput()->withErrors(['kapasitas' => 'Sistem sedang sibuk memproses pesanan lain di tanggal tersebut. Silakan coba lagi.']);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['kapasitas' => $e->getMessage()]);
        }

        return redirect()->route('pesanan.bayar', $pesanan->id_pesanan)
            ->with('success', 'Pesanan Nasi Box berhasil dibuat!');
    }

    // ADMIN GET /admin/pesanan/nasibox
    public function index(Request $request)
    {
        $query = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'status_pesanan', 'status_pembayaran', 'pembayaran', 'pelanggan', 'pengiriman.status_pengiriman'])
            ->where('jenis_pesanan_id', 3)
            ->latest('dibuat_pada');

        if ($request->has('search')) {
            $query->where('id_pesanan', 'like', "%{$request->search}%");
        }

        $status = $request->status ?? 'all';
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
            'pengiriman',
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
            'pengiriman',
            'pembayaran',
            'status_pesanan',
        ])->findOrFail($id);

        $kebutuhanBahan = $this->hitungKebutuhanBahan($pesanan);

        $pdf = Pdf::loadView('pdf.invoice', compact('pesanan', 'kebutuhanBahan'));

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
            $bahan = BahanBaku::with(['satuan', 'stok_harian'])->find($item['bahan_baku_id']);
            if ($bahan) {
                $stokHarian = (float) ($bahan->stok_harian->jumlah_stok ?? 0);
                $totalKebutuhan = (float) $item['kebutuhan'];
                $result[] = [
                    'id' => $bahan->id,
                    'kode_bahan' => $bahan->id_bahan_baku ?? ('BB-' . str_pad($bahan->id, 3, '0', STR_PAD_LEFT)),
                    'nama_bahan' => $bahan->nama_bahan,
                    'total_kebutuhan' => $totalKebutuhan,
                    'stok_harian' => $stokHarian,
                    'stok_katering' => $stokHarian, // fallback compatibility
                    'satuan' => optional($bahan->satuan)->nama_satuan ?? optional($bahan->satuan)->singkatan ?? 'Gram',
                    'cukup' => $stokHarian >= $totalKebutuhan,
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
        $pesanan = Pesanan::with('pengiriman')->findOrFail($id);
        $request->validate(['status' => 'required']);

        $roleName = Auth::user()->peran?->nama_peran ?? '';
        $isPemilik = in_array($roleName, ['Pemilik', 'Admin', 'Super Admin']);
        $isDapur = in_array($roleName, ['Dapur', 'Tim Dapur', 'Koki']) || (method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Dapur', 'Tim Dapur', 'Koki'));
        $isManajer = in_array($roleName, ['Manajer', 'Manager']);

        if ($isManajer) {
            return back()->with('error', 'Aktor Manajer tidak memiliki hak untuk mengubah status pesanan konsumen.');
        }

        $status = (int) $request->status;
        $currentStatus = (int) $pesanan->status_pesanan_id;

        // 1. Aksi Pembatalan (Status 6 - Dibatalkan)
        if ($status === 6) {
            if (!$isPemilik) {
                return back()->with('error', 'Hanya Pemilik yang dapat membatalkan pesanan.');
            }
            if ($currentStatus === 5) {
                return back()->with('error', 'Pesanan yang sudah selesai tidak dapat dibatalkan.');
            }

            $alasan = $request->alasan_batal;
            $pesanan->update([
                'status_pesanan_id' => 6,
                'catatan' => $alasan ? trim($pesanan->catatan.' [BATAL: '.$alasan.']') : $pesanan->catatan,
            ]);

            app(OrderService::class)->restoreStockPesanan($pesanan);

            if ($pesanan->pengiriman) {
                $pesanan->pengiriman->update(['status_pengiriman_id' => 5]); // Dibatalkan
            }

            return back()->with('success', 'Pesanan Nasi Box berhasil dibatalkan.');
        }

        // 2. Transisi: MENUNGGU (1) -> DIKONFIRMASI (2)
        if ($status === 2) {
            if (!$isPemilik) {
                return back()->with('error', 'Hanya Pemilik yang dapat mengonfirmasi pesanan.');
            }
            if (!in_array($pesanan->status_pembayaran_id, [3, 4, 5])) {
                return back()->with('error', 'Pesanan tidak bisa dikonfirmasi karena pembayaran DP belum diverifikasi.');
            }
            $pesanan->update(['status_pesanan_id' => 2]);
            return back()->with('success', 'Pesanan Nasi Box berhasil dikonfirmasi.');
        }

        // 3. Transisi: DIKONFIRMASI (2) -> TERJADWAL (7)
        if ($status === 7) {
            if (!$isPemilik) {
                return back()->with('error', 'Hanya Pemilik yang dapat menjadwalkan pesanan.');
            }
            $pesanan->update(['status_pesanan_id' => 7]);
            if ($pesanan->pengiriman) {
                $pesanan->pengiriman->update(['status_pengiriman_id' => 1]); // Dijadwalkan
            }
            return back()->with('success', 'Pesanan Nasi Box berhasil ditandai Terjadwal.');
        }

        // 4. Transisi: DIKONFIRMASI (2) / TERJADWAL (7) -> DIPROSES (3)
        if ($status === 3) {
            if (!$isDapur && !$isPemilik) {
                return back()->with('error', 'Hanya Tim Dapur yang dapat memulai proses memasak pesanan.');
            }
            if (!in_array($pesanan->status_pembayaran_id, [3, 4, 5])) {
                return back()->with('error', 'Pesanan hanya bisa masuk ke dapur setelah pembayaran DP atau Lunas diverifikasi.');
            }

            if ($pesanan->status_pesanan_id < 3) {
                try {
                    app(OrderService::class)->potongStokPesanan($pesanan);
                } catch (\RuntimeException $e) {
                    return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage() . ' Silakan tambah stok bahan terlebih dahulu.');
                }
            }

            $pesanan->update(['status_pesanan_id' => 3]);
            return back()->with('success', 'Pesanan Nasi Box sedang diproses oleh dapur.');
        }

        // 5. Transisi: DIPROSES (3) -> SIAP (4) (Pesanan Siap)
        if ($status === 4) {
            if (!$isDapur && !$isPemilik) {
                return back()->with('error', 'Hanya Tim Dapur yang dapat menandai pesanan selesai disiapkan.');
            }

            $pesanan->update(['status_pesanan_id' => 4]);

            // Jika ada pengiriman, otomatis jadikan SIAP_DIKIRIM (2)
            if ($pesanan->pengiriman) {
                $pesanan->pengiriman->update(['status_pengiriman_id' => 2]); // Siap Dikirim
            }

            return back()->with('success', 'Pesanan Nasi Box telah siap.');
        }

        // 6. Transisi: SIAP (4) -> SELESAI (5) (Khusus Ambil Sendiri)
        if ($status === 5) {
            if (!$isPemilik) {
                return back()->with('error', 'Hanya Pemilik yang dapat menyelesaikan pesanan.');
            }
            if ($pesanan->pengiriman && $pesanan->pengiriman->status_pengiriman_id !== 4) {
                return back()->with('error', 'Pesanan dengan metode pengantaran akan otomatis selesai ketika status pengiriman Terkirim.');
            }

            $pesanan->update(['status_pesanan_id' => 5]);
            return back()->with('success', 'Pesanan Nasi Box telah selesai.');
        }

        return back()->with('error', 'Perubahan status tidak diizinkan.');
    }

    /** Verifikasi bukti pembayaran Nasi Box */
    public function verifikasiPembayaran(Request $request, $buktiId)
    {
        $pembayaran = Pembayaran::findOrFail($buktiId);
        $pesanan    = Pesanan::findOrFail($pembayaran->pesanan_id);

        $request->validate(['catatan_admin' => 'nullable|string|max:255']);

        $pembayaran->update([
            'status_verifikasi'  => 'diterima',
            'diverifikasi_oleh'  => Auth::id(),
            'tanggal_verifikasi' => now(),
            'catatan_verifikasi' => $request->filled('catatan_admin') ? $request->catatan_admin : 'Diverifikasi oleh admin',
        ]);

        if ($pembayaran->jenis_pembayaran === 'uang_muka') {
            // Set batas pelunasan H-3 dari tanggal acara
            $batasPelunasan = null;
            if ($pesanan->jadwal_pesanan && $pesanan->jadwal_pesanan->tanggal_acara) {
                $batasPelunasan = \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->subDays(3)->endOfDay();
            }

            $pesanan->update([
                'status_pembayaran_id' => 3, // Menunggu Pelunasan
                'status_pesanan_id'    => 2, // Dikonfirmasi
                'batas_pelunasan'      => $batasPelunasan
            ]);
        } else {
            $pesanan->update([
                'status_pembayaran_id' => 5, // Lunas
                'status_pesanan_id'    => $pesanan->status_pesanan_id == 1 ? 2 : $pesanan->status_pesanan_id,
            ]);
        }

        return back()->with('success', 'Bukti pembayaran #'.$pembayaran->kode_pembayaran.' berhasil diverifikasi.');
    }

    /** Tolak bukti pembayaran Nasi Box */
    public function tolakPembayaran(Request $request, $buktiId)
    {
        $pembayaran = Pembayaran::findOrFail($buktiId);
        $pesanan    = Pesanan::findOrFail($pembayaran->pesanan_id);

        $request->validate(['catatan_admin' => 'nullable|string|max:500']);

        $pembayaran->update([
            'status_verifikasi'  => 'ditolak',
            'diverifikasi_oleh'  => Auth::id(),
            'tanggal_verifikasi' => now(),
            'catatan_verifikasi' => $request->filled('catatan_admin') ? $request->catatan_admin : 'Bukti ditolak oleh admin',
        ]);

        if ($pembayaran->jenis_pembayaran === 'uang_muka') {
            $pesanan->update(['status_pembayaran_id' => 1]); // Kembali ke Menunggu DP
        } else {
            $pesanan->update(['status_pembayaran_id' => 3]); // Kembali ke Menunggu Pelunasan
        }

        return back()->with('error', 'Bukti pembayaran #'.$pembayaran->kode_pembayaran.' ditolak.');
    }
}
