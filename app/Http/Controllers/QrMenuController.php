<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Services\DineInService;
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

        $pengaturan = \App\Models\PengaturanTransaksi::first();

        return view('admin.menu.qr.index', compact('kategoris', 'menus', 'selectedMeja', 'pengaturan'));
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

            $pesanan = $this->dineInService->createOrder(
                $request->meja_id,
                $request->nama_konsumen,
                $request->jumlah_tamu ?? 1,
                $request->items,
                $staffId,
                'self_order',
                $request->nomor_hp
            );

            // Kirim notifikasi ke kasir/manajer/pemilik/dapur
            $meja = \App\Models\Meja::find($request->meja_id);
            $pesananNorm = \App\Models\Pesanan::where('id_pesanan', $pesanan->kode_pesanan)->first();
            if ($pesananNorm && $meja) {
                $users = \App\Models\Pengguna::whereHas('peran', function ($q) {
                    $q->whereIn('nama_peran', ['Pemilik', 'Admin', 'Manajer', 'Kasir', 'Dapur']);
                })->get();
                $qty = collect($request->items)->sum('qty');
                if (! $qty && $pesananNorm->detail_pesanan) {
                    $qty = $pesananNorm->detail_pesanan->sum('jumlah');
                }
                $pesananMessage = "Pesanan Dine-In #{$pesanan->kode_pesanan} sebanyak {$qty} porsi telah masuk dari Meja {$meja->nomor_meja} atas nama {$request->nama_konsumen}.";
                \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\PesananBaru($pesananNorm, "Pesanan Baru", $pesananMessage, route('pos.dinein.index')));
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikirim. Anda dapat melihat detail pesanan atau melakukan pembayaran kasir.',
                'pesanan_id' => $pesananNorm ? $pesananNorm->id : $pesanan->id,
                'kode_pesanan' => $pesanan->kode_pesanan,
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
    public function downloadReceipt($id)
    {
        $pesanan = \App\Models\Pesanan::with(['meja', 'pelanggan', 'detail_pesanan.menu', 'kasir'])->find($id);
        if (!$pesanan) {
            $pesanan = \App\Models\Pesanan::with(['meja', 'pelanggan', 'detail_pesanan.menu', 'kasir'])->where('id_pesanan', $id)->firstOrFail();
        }

        $pengaturan = \App\Models\PengaturanTransaksi::first();

        return view('admin.pos.pesanan.print-nota', compact('pesanan', 'pengaturan'));
    }
}
