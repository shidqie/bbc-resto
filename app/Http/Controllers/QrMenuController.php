<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Services\DineInService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrMenuController extends Controller
{
    protected $dineInService;

    protected $midtransService;

    public function __construct(DineInService $dineInService, MidtransService $midtransService)
    {
        $this->dineInService = $dineInService;
        $this->midtransService = $midtransService;
    }

    /**
     * Halaman Scanner QR Code Mandiri
     */
    public function scanner()
    {
        return view('menu.qr-menu.scanner');
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
            return view('menu.qr-menu.scan-required');
        }

        // Fetch kategori (urut sesuai id: Paket Nasi Liwet → Minuman Non-Coffee)
        $kategoris = KategoriMenu::orderBy('id', 'asc')->get();

        // Fetch menu Dine-In aktif
        $rawMenus = Menu::with(['kategori_menu'])
            ->where(function ($q) {
                $q->where('jenis_menu_id', 1)->orWhereNull('jenis_menu_id');
            })
            ->where('status_aktif', true)
            ->get();

        $menus = $rawMenus->map(fn ($m) => [
            'id' => $m->id,
            'nama' => $m->nama_menu ?? $m->nama ?? 'Menu',
            'harga' => (float) ($m->harga_jual ?? 0),
            'foto' => $m->foto,
            'deskripsi' => $m->deskripsi,
            'kategori_menu_id' => $m->kategori_menu_id,
            'status' => $m->status_aktif ? 'aktif' : 'habis',
            'is_habis' => false,
        ]);

        return view('menu.qr-menu.index', compact('kategoris', 'menus', 'selectedMeja'));
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
            $staffId = 1; // Self-order by customer → gunakan default system staff

            $pesanan = $this->dineInService->createOrder(
                $request->meja_id,
                $request->nama_konsumen,
                $request->jumlah_tamu ?? 1,
                $request->items,
                $staffId
            );

            $kodePesanan = $pesanan->kode_pesanan ?? 'DIN-'.$pesanan->id;
            $metode = $request->metode_pembayaran ?? 'kasir';
            $qrisData = null;

            if ($metode === 'qris') {
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $m = Menu::find($item['menu_id']);
                    if ($m) {
                        $totalAmount += (($m->harga_jual ?? 0) * $item['qty']);
                    }
                }

                $qrisData = $this->midtransService->createQrisPayment(
                    (int) $totalAmount,
                    $kodePesanan,
                    ['first_name' => $request->nama_konsumen]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $metode === 'qris'
                    ? 'Pesanan dibuat! Silakan scan QRIS untuk membayar.'
                    : 'Pesanan berhasil dikirim ke kasir!',
                'kode_pesanan' => $kodePesanan,
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran' => $metode,
                'qris' => $qrisData,
            ]);

        } catch (\Exception $e) {
            Log::error('QR Menu storeOrder Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: '.$e->getMessage(),
            ], 400);
        }
    }
}
