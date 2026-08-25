<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Services\DineInService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QrMenuController extends Controller
{
    protected $dineInService;

    public function __construct(DineInService $dineInService)
    {
        $this->dineInService = $dineInService;
    }

    /**
     * Halaman Scanner QR Code Mandiri
     */
    public function scanner()
    {
        $mejas = Meja::orderBy('id')->get();

        return view('admin.menu.qr.scanner', compact('mejas'));
    }

    /**
     * Halaman menu QR — hanya bisa diakses via QR Code meja (dengan token).
     * Tanpa token valid, konsumen diarahkan ke halaman "scan QR dulu".
     */
    public function index(Request $request, $token = null)
    {
        $selectedMeja = null;

        if (! $token) {
            $token = $request->query('token') ?? $request->query('meja') ?? $request->query('table');
        }

        // Resolve meja dari token QR, ID, atau Nomor Meja
        if ($token) {
            $selectedMeja = Meja::where('qr_token', $token)
                ->orWhere('id', $token)
                ->orWhere('nomor_meja', $token)
                ->orWhere('nomor_meja', 'Meja '.$token)
                ->orWhere('nomor_meja', 'meja '.$token)
                ->first();
        }

        // Jika tidak ada token atau token tidak valid → tampilkan halaman "scan QR dulu"
        if (! $selectedMeja) {
            return view('admin.menu.qr.scan-required');
        }

        $kebutuhanBahanService = app(\App\Services\KebutuhanBahanService::class);

        // Fetch menu Dine-In aktif, beserta resep & stok bahan baku
        $rawMenus = Menu::with(['kategori_menu', 'resep_menu.bahan_baku', 'item_paket'])
            ->where(function ($q) {
                $q->where('jenis_menu_id', 1)->orWhereNull('jenis_menu_id');
            })
            ->where('status_aktif', true)
            ->get();

        // Fetch kategori yang memuat menu Dine-In aktif (sama persis dengan landing page)
        $kategoriIds = $rawMenus->pluck('kategori_menu_id')->filter()->unique();
        $kategoris = KategoriMenu::whereIn('id', $kategoriIds)->orderBy('id', 'asc')->get();

        // Hitung menu yang habis berdasarkan stok bahan baku harian (menggunakan KebutuhanBahanService)
        $menuHabisIds = [];
        foreach ($rawMenus as $menu) {
            $porsi = $kebutuhanBahanService->porsiTersedia($menu, \App\Models\StokBahan::JENIS_HARIAN);
            if ($porsi < 1) {
                $menuHabisIds[] = $menu->id;
            }
        }

        $menus = $rawMenus->map(function ($m) use ($menuHabisIds) {
            $isHabis = in_array($m->id, $menuHabisIds) || ! $m->status_aktif || $m->status === 'habis';
            return [
                'id'              => $m->id,
                'nama'            => $m->nama_menu ?? $m->nama ?? 'Menu',
                'harga'           => (float) ($m->harga_jual ?? 0),
                'foto'            => $m->foto,
                'deskripsi'       => $m->deskripsi,
                'kategori_menu_id'=> $m->kategori_menu_id,
                'status'          => $isHabis ? 'habis' : 'aktif',
                'is_habis'        => $isHabis,
            ];
        });

        // Cek apakah meja memiliki sesi pesanan aktif (Dine-in belum lunas/selesai)
        $activePesanan = \App\Models\Pesanan::with(['detail_pesanan.menu'])
            ->where('meja_id', $selectedMeja->id)
            ->where('jenis_pesanan_id', 1)
            ->whereNotIn('status_pesanan_id', [5, 6])
            ->where(function ($q) {
                $q->whereNull('status_pembayaran_id')
                    ->orWhereNotIn('status_pembayaran_id', [2, 5]);
            })
            ->latest('id')
            ->first();

        $pengaturan = \App\Models\PengaturanTransaksi::first();

        return view('admin.menu.qr.index', compact('kategoris', 'menus', 'selectedMeja', 'pengaturan', 'activePesanan'));
    }

    /**
     * Simpan pesanan dari QR Self-Order.
     */
    public function storeOrder(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:meja,id',
            'nama_konsumen' => 'required|string|max:255',
            'nomor_hp' => 'nullable|string|max:20',
            'jumlah_tamu' => 'nullable|integer|min:1',
            'metode_pembayaran' => 'nullable|string|in:kasir,qris',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menu,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string',
        ]);

        $kebutuhanBahanService = app(\App\Services\KebutuhanBahanService::class);
        foreach ($request->items as $item) {
            $menu = Menu::find($item['menu_id']);
            if (! $menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu tidak ditemukan.',
                ], 400);
            }
            if (! $kebutuhanBahanService->bahanCukup($menu, (float) $item['qty'], null, \App\Models\StokBahan::JENIS_HARIAN)) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok bahan baku untuk menu '{$menu->nama_menu}' tidak mencukupi / habis. Silakan sesuaikan pesanan Anda.",
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            $staffId = null; // Self-order by customer → tidak ada pelayan_id

            // Cek apakah sebelumnya sudah ada pesanan aktif
            $hadActiveBefore = \App\Models\Pesanan::where('meja_id', $request->meja_id)
                ->where('jenis_pesanan_id', 1)
                ->whereNotIn('status_pesanan_id', [5, 6])
                ->where(function ($q) {
                    $q->whereNull('status_pembayaran_id')
                        ->orWhereNotIn('status_pembayaran_id', [2, 5]);
                })
                ->exists();

            $pesanan = $this->dineInService->createOrder(
                $request->meja_id,
                $request->nama_konsumen,
                $request->jumlah_tamu ?? 1,
                $request->items,
                $staffId,
                'self_order',
                $request->nomor_hp
            );

            $kodePesananFinal = $pesanan->id_pesanan ?? $pesanan->kode_pesanan;

            // Kirim notifikasi ke kasir/manajer/pemilik/dapur
            $meja = \App\Models\Meja::find($request->meja_id);
            if ($pesanan && $meja) {
                $users = \App\Models\Pengguna::whereHas('peran', function ($q) {
                    $q->whereIn('nama_peran', ['Pemilik', 'Admin', 'Manajer', 'Kasir', 'Dapur']);
                })->get();
                $qty = collect($request->items)->sum('qty');
                if (! $qty && $pesanan->detail_pesanan) {
                    $qty = $pesanan->detail_pesanan->sum('jumlah');
                }
                $notifTitle = $hadActiveBefore ? "Pesanan Tambahan Masuk" : "Pesanan Baru Masuk";
                $pesananMessage = $hadActiveBefore
                    ? "Pesanan Tambahan #{$kodePesananFinal} sebanyak {$qty} porsi telah masuk dari Meja {$meja->nomor_meja}."
                    : "Pesanan Dine-In #{$kodePesananFinal} sebanyak {$qty} porsi telah masuk dari Meja {$meja->nomor_meja} atas nama {$request->nama_konsumen}.";

                \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\PesananBaru($pesanan, $notifTitle, $pesananMessage, route('pos.dinein.index')));
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $hadActiveBefore ? 'Pesanan tambahan berhasil ditambahkan ke sesi pesanan Anda.' : 'Pesanan berhasil dikirim.',
                'pesanan_id' => $pesanan->id,
                'kode_pesanan' => $kodePesananFinal,
                'total_tagihan' => $pesanan->total_tagihan,
                'is_tambahan' => $hadActiveBefore,
                'status' => 'pending'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order submission error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unduh / Cetak Struk Bukti Pesanan Self-Order QR (Public)
     */
    public function downloadReceipt(Request $request, $id)
    {
        $pesanan = \App\Models\Pesanan::with(['meja', 'pelanggan', 'detail_pesanan.menu', 'kasir'])->find($id);
        if (!$pesanan) {
            $pesanan = \App\Models\Pesanan::with(['meja', 'pelanggan', 'detail_pesanan.menu', 'kasir'])->where('id_pesanan', $id)->firstOrFail();
        }

        $pengaturan = \App\Models\PengaturanTransaksi::first();

        if ($request->query('preview') === '1') {
            return view('admin.menu.qr.bukti-pesanan', compact('pesanan', 'pengaturan'));
        }

        $itemCount = $pesanan->detail_pesanan ? $pesanan->detail_pesanan->count() : 1;
        $height = max(340, 280 + ($itemCount * 24));

        $pdf = Pdf::loadView('admin.menu.qr.bukti-pesanan-pdf', compact('pesanan', 'pengaturan'))
            ->setPaper([0, 0, 226.77, $height], 'portrait');

        return $pdf->download('Bukti-Pesanan-' . $pesanan->id_pesanan . '.pdf');
    }
}
