<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Services\DineInService;
use App\Services\MidtransService;
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

        // Resolve meja dari token QR
        if ($token) {
            $selectedMeja = Meja::where('qr_token', $token)->first();
        }

        // Jika tidak ada token atau token tidak valid → tampilkan halaman "scan QR dulu"
        if (! $selectedMeja) {
            return view('admin.menu.qr.scan-required');
        }

        // Fetch kategori (urut sesuai id: Paket Nasi Liwet → Minuman Non-Coffee)
        $kategoris = KategoriMenu::orderBy('id', 'asc')->get();

        // Fetch menu Dine-In aktif, beserta resep & stok bahan baku
        $rawMenus = Menu::with(['kategori_menu', 'resep_menu.bahan_baku.stok_relasi'])
            ->where(function ($q) {
                $q->where('jenis_menu_id', 1)->orWhereNull('jenis_menu_id');
            })
            ->where('status_aktif', true)
            ->get();

        // Hitung menu yang habis berdasarkan stok bahan baku (sama seperti DineInController)
        $menuHabisIds = $rawMenus->filter(function ($menu) {
            if ($menu->resep_menu->isEmpty()) {
                return false; // Jika tidak ada resep, anggap tersedia
            }
            foreach ($menu->resep_menu as $resep) {
                $bahan = $resep->bahan_baku;
                if (!$bahan) continue;
                $stok = (float) ($bahan->stok_relasi->jumlah_stok ?? 0);
                $kebutuhan = (float) ($resep->jumlah ?? 0);
                if ($kebutuhan > 0 && $stok < $kebutuhan) {
                    return true; // Stok tidak cukup → menu habis
                }
            }
            return false;
        })->pluck('id')->toArray();

        $menus = $rawMenus->map(fn ($m) => [
            'id'              => $m->id,
            'nama'            => $m->nama_menu ?? $m->nama ?? 'Menu',
            'harga'           => (float) ($m->harga_jual ?? 0),
            'foto'            => $m->foto,
            'deskripsi'       => $m->deskripsi,
            'kategori_menu_id'=> $m->kategori_menu_id,
            'status'          => (in_array($m->id, $menuHabisIds)) ? 'habis' : ($m->status_aktif ? 'aktif' : 'habis'),
            'is_habis'        => in_array($m->id, $menuHabisIds),
        ]);

        return view('admin.menu.qr.index', compact('kategoris', 'menus', 'selectedMeja'));
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

        try {
            DB::beginTransaction();

            $staffId = 1; // Self-order by customer → gunakan default system staff

            $pesanan = $this->dineInService->createOrder(
                $request->meja_id,
                $request->nama_konsumen,
                $request->jumlah_tamu ?? 1,
                $request->items,
                $staffId
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikirim. Anda dapat melihat detail pesanan atau melakukan pembayaran kasir.',
                'pesanan_id' => $pesanan->id,
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
}
