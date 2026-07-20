<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\KategoriMenu;
use App\Services\DineInService;

class DineInController extends Controller
{
    protected $dineInService;

    public function __construct(DineInService $dineInService)
    {
        $this->dineInService = $dineInService;
    }

    public function index()
    {
        $mejas = Meja::orderBy('id', 'asc')->get();
        $menus = Menu::with('kategori')->where('status', 'tersedia')->get();
        $kategoris = KategoriMenu::all();
        
        return view('pos.dinein.index', compact('mejas', 'menus', 'kategoris'));
    }

    public function order($mejaId)
    {
        $meja = Meja::findOrFail($mejaId);
        $menus = Menu::with('kategori')->get();
        
        // Buka meja jika masih kosong
        try {
            $pesanan = $this->dineInService->bukaMeja($meja->id, auth()->id());
        } catch (\Exception $e) {
            return redirect()->route('pos.dinein.index')->with('error', $e->getMessage());
        }

        return view('pos.dinein.order', compact('meja', 'menus', 'pesanan'));
    }

    public function storePosOrder(Request $request)
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
            $pesanan = $this->dineInService->createOrder(
                $request->meja_id,
                $request->nama_konsumen,
                $request->jumlah_tamu ?? 1,
                $request->items,
                auth()->id()
            );

            return response()->json([
                'success' => true, 
                'message' => 'Pesanan berhasil disimpan.',
                'pesanan_id' => $pesanan->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function clearTable($mejaId)
    {
        $meja = Meja::findOrFail($mejaId);
        $meja->update(['status' => 'kosong']);
        return redirect()->back()->with('success', 'Meja ' . $meja->nomor_meja . ' berhasil dikosongkan.');
    }
}
