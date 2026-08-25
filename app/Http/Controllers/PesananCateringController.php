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

        $komponenMap = [];
        foreach ($pakets as $p) {
            $komponenMap[$p->id] = $this->getKomponen($p->id)->getData();
        }

        return view('pelanggan.pesanan.catering.create', compact('pakets', 'selectedPaketId', 'komponenMap'));
    }

    // GET /pesan/catering/komponen/{paketId}
    public function getKomponen($paketId)
    {
        try {
            $paket = Menu::with('komponen_paket.opsi.menu')->find($paketId);
            if (!$paket) {
                return response()->json([]);
            }

            return response()->json(
                $paket->komponen_paket->map(function ($k) {
                    if (in_array($k->tipe_komponen, ['wajib', 'tetap'])) {
                        $menu = $k->menu_id_terkait ? \App\Models\Menu::find($k->menu_id_terkait) : null;
                        $foto = $menu ? $menu->foto : null;
                        if (!$foto) {
                            $matched = \App\Models\Menu::where('nama_menu', $k->nama_komponen)->whereNotNull('foto')->first();
                            $foto = $matched ? $matched->foto : null;
                        }
                        return [
                            'id' => $k->id,
                            'nama_komponen' => $k->nama_komponen,
                            'tipe' => 'fixed',
                            'opsi' => [[
                                'menu' => [
                                    'id' => $menu ? $menu->id : $k->id,
                                    'nama' => $menu ? ($k->nama_komponen !== $menu->nama_menu && $k->nama_komponen ? $k->nama_komponen : $menu->nama_menu) : $k->nama_komponen,
                                    'foto' => $foto,
                                ]
                            ]]
                        ];
                    }

                    return [
                        'id' => $k->id,
                        'nama_komponen' => $k->nama_komponen,
                        'tipe' => $k->tipe_komponen === 'semua_didapat' ? 'fixed' : 'choice',
                        'opsi' => $k->opsi->map(function ($o) {
                            $foto = $o->foto ?? ($o->menu ? $o->menu->foto : null);
                            if (!$foto) {
                                $matched = \App\Models\Menu::where('nama_menu', $o->nama_pilihan)->whereNotNull('foto')->first();
                                $foto = $matched ? $matched->foto : null;
                            }

                            return [
                                'menu' => [
                                    'id' => $o->id,
                                    'nama' => $o->nama_pilihan,
                                    'foto' => $foto,
                                ],
                            ];
                        })->values()->all(),
                    ];
                })
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getKomponen error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            // Normalisasi: data pemesan disimpan di tabel pelanggan tanpa duplikasi
            $nomorTeleponNorm = \App\Support\WhatsAppNumber::normalize($request->kontak);
            $alamatInput = $request->alamat_pengiriman ?? $request->lokasi_pengiriman ?? $request->alamat ?? '-';

            if (Auth::guard('pelanggan')->check()) {
                $pelanggan = Auth::guard('pelanggan')->user();
                if ($alamatInput !== '-' && (empty($pelanggan->alamat) || $pelanggan->alamat === '-')) {
                    $pelanggan->update(['alamat' => $alamatInput]);
                }
            } else {
                $pelanggan = Pelanggan::where('nomor_telepon', $nomorTeleponNorm)
                    ->orWhere('nomor_telepon', $request->kontak)
                    ->first();

                if ($pelanggan) {
                    $updates = [];
                    if (!empty($request->nama_pemesan) && (empty($pelanggan->nama) || $pelanggan->nama === 'Umum')) {
                        $updates['nama'] = $request->nama_pemesan;
                    }
                    if ($alamatInput !== '-' && (empty($pelanggan->alamat) || $pelanggan->alamat === '-')) {
                        $updates['alamat'] = $alamatInput;
                    }
                    if (!empty($updates)) {
                        $pelanggan->update($updates);
                    }
                } else {
                    $pelanggan = Pelanggan::create([
                        'nomor_telepon' => $nomorTeleponNorm,
                        'nama' => $request->nama_pemesan,
                        'alamat' => $alamatInput,
                    ]);
                }
            }


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

            // Buat sesi pembayaran awal 12 jam (Lunas 100% atau DP 50%)
            $isLunasOpsi = $request->opsi_pembayaran === 'lunas';
            $nominalBayarAwal = $isLunasOpsi ? $totalTagihan : round($totalTagihan * 0.5);
            $jenisBayarAwal = $isLunasOpsi ? 'pelunasan' : 'uang_muka';

            Pembayaran::create([
                'kode_pembayaran' => 'PAY-' . strtoupper(uniqid()),
                'pesanan_id' => $pesanan->id,
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
                $qty = $pesanan->detail_pesanan->sum('jumlah') ?: $pesanan->detail_pesanan->sum('kuantitas');
                $namaPemesan = $pelanggan->nama ?? ($pesanan->pelanggan->nama ?? null);
                if (!$namaPemesan && !empty($pesanan->catatan)) {
                    if (preg_match('/^Pemesan:\s*(.+)$/m', $pesanan->catatan, $m)) {
                        $namaPemesan = trim($m[1]);
                    } else {
                        $namaPemesan = trim(explode('|', $pesanan->catatan)[0]);
                    }
                }
                $atasNama = $namaPemesan ? " atas nama {$namaPemesan}" : "";
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\PesananBaru(
                    $pesanan,
                    "Pesanan Baru",
                    "Pesanan Katering #{$pesanan->id_pesanan} sebanyak {$qty} porsi{$atasNama} telah masuk dan menunggu konfirmasi.", 
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

        $query = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'status_pesanan', 'status_pembayaran', 'pembayaran', 'pelanggan', 'pengiriman.status_pengiriman'])
            ->where('jenis_pesanan_id', 2)
            ->latest('dibuat_pada');

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
            $bahan = \App\Models\BahanBaku::with(['stok_catering_balance', 'satuan'])->find($item['bahan_baku_id']);
            if (! $bahan) {
                continue;
            }
            $stokKatering = (float) ($bahan->stok_catering_balance->jumlah_stok ?? 0);
            $totalKebutuhan = (float) $item['kebutuhan'];
            $kebutuhan[$bahan->id] = [
                'id' => $bahan->id,
                'kode_bahan' => $bahan->id_bahan_baku ?? ('BB-' . str_pad($bahan->id, 3, '0', STR_PAD_LEFT)),
                'nama_bahan' => $bahan->nama_bahan,
                'satuan' => optional($bahan->satuan)->nama_satuan ?? optional($bahan->satuan)->singkatan ?? 'Gram',
                'stok_katering' => $stokKatering,
                'total_kebutuhan' => $totalKebutuhan,
                'cukup' => $stokKatering >= $totalKebutuhan,
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
            // Set batas pelunasan H-3 dari tanggal acara
            $batasPelunasan = null;
            if ($pesanan->jadwal_pesanan && $pesanan->jadwal_pesanan->tanggal_acara) {
                $batasPelunasan = \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->subDays(3)->endOfDay();
            }

            $pesanan->update([
                'status_pembayaran_id' => 3, // Menunggu Pelunasan
                'status_pesanan_id' => 2, // Dikonfirmasi
                'batas_pelunasan' => $batasPelunasan
            ]);
        } else {
            $pesanan->update([
                'status_pembayaran_id' => 5, // Lunas
                'status_pesanan_id' => $pesanan->status_pesanan_id == 1 ? 2 : $pesanan->status_pesanan_id,
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

        $pdf = Pdf::loadView('pdf.invoice', compact('pesanan', 'type', 'kodePesanan'));

        return $pdf->download('bukti-pesanan-'.$pesanan->id_pesanan.'.pdf');
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

            return back()->with('success', 'Pesanan katering berhasil dibatalkan.');
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
            return back()->with('success', 'Pesanan katering berhasil dikonfirmasi.');
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
            return back()->with('success', 'Pesanan katering berhasil ditandai Terjadwal.');
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
            return back()->with('success', 'Pesanan katering sedang diproses oleh dapur.');
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

            return back()->with('success', 'Pesanan katering telah siap.');
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
            return back()->with('success', 'Pesanan katering telah selesai.');
        }

        return back()->with('error', 'Perubahan status tidak diizinkan.');
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
