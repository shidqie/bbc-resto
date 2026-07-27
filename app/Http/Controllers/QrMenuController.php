<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Services\DineInService;
use Illuminate\Support\Facades\Log;

class QrMenuController extends Controller
{
    protected $dineInService;

    public function __construct(DineInService $dineInService)
    {
        $this->dineInService = $dineInService;
    }

    public function index(Request $request)
    {
        // Fetch active categories
        $kategoris = KategoriMenu::orderBy('nama', 'asc')->get();

        // Fetch menus for Dine-In
        $menus = Menu::with(['kategori', 'resep.bahanBaku'])
            ->where(function ($query) {
                $query->where('jenis_menu', 'dine_in')
                      ->orWhereNull('jenis_menu');
            })
            ->get();

        // Fetch all tables
        $mejas = Meja::orderBy('id', 'asc')->get();

        // Check if table parameter is present
        $selectedMejaParam = $request->query('meja') ?? $request->query('meja_id');
        $selectedMeja = null;

        if ($selectedMejaParam) {
            $selectedMeja = Meja::where('id', $selectedMejaParam)
                ->orWhere('nomor_meja', $selectedMejaParam)
                ->orWhere('nomor_meja', 'Meja ' . $selectedMejaParam)
                ->first();
        }

        return view('qr-menu.index', compact('kategoris', 'menus', 'mejas', 'selectedMeja'));
    }

    public function scanner()
    {
        $mejas = Meja::orderBy('id', 'asc')->get();
        return view('qr-menu.scanner', compact('mejas'));
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:mejas,id',
            'nama_konsumen' => 'required|string|max:255',
            'jumlah_tamu' => 'nullable|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string'
        ]);

        try {
            // Default staff user ID if order placed directly by customer QR scanning
            $staffId = auth()->check() ? auth()->id() : 1;

            $pesanan = $this->dineInService->createOrder(
                $request->meja_id,
                $request->nama_konsumen,
                $request->jumlah_tamu ?? 1,
                $request->items,
                $staffId
            );

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikirim ke kasir & dapur!',
                'kode_pesanan' => $pesanan->kode_pesanan ?? 'DIN-' . $pesanan->id,
                'pesanan_id' => $pesanan->id
            ]);
        } catch (\Exception $e) {
            Log::error('QR Menu Store Order Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 400);
        }
    }

    public function panggilPelayan(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:mejas,id',
            'alasan' => 'nullable|string|max:255'
        ]);

        $meja = Meja::find($request->meja_id);

        return response()->json([
            'success' => true,
            'message' => 'Panggilan pelayan dikirim! Staf kami akan segera mendatangi ' . ($meja ? 'Meja ' . $meja->nomor_meja : 'meja Anda') . '.'
        ]);
    }
}
