<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\KategoriMenu;
use App\Models\PesananDinein;
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
        $menus = Menu::with(['kategori', 'resep.bahanBaku'])
            ->where(function($query) {
                $query->where('jenis_menu', 'dine_in')
                      ->orWhereNull('jenis_menu');
            })
            ->get();

        $kategoriIds = $menus->pluck('kategori_menu_id')->filter()->unique();
        $kategoris = KategoriMenu::whereIn('id', $kategoriIds)->get();

        // Shift Kasir Aktif
        $activeShift = \App\Models\ShiftKasir::where('user_id', auth()->id())
            ->where('status', 'buka')
            ->latest()
            ->first();

        // Open Bills (Pesanan Menggantung Menunggu Pembayaran)
        $openBills = PesananDinein::with(['meja', 'items.menu'])
            ->where('status', 'menunggu_pembayaran')
            ->orderBy('dibuka_pada', 'desc')
            ->get();
        
        return view('pos.dinein.index', compact('mejas', 'menus', 'kategoris', 'activeShift', 'openBills'));
    }

    public function order($mejaId)
    {
        $meja = Meja::findOrFail($mejaId);
        $menus = Menu::with(['kategori', 'resep.bahanBaku'])
            ->where(function($query) {
                $query->where('jenis_menu', 'dine_in')
                      ->orWhereNull('jenis_menu');
            })
            ->get();
        
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
        
        // Batalkan pesanan gantung jika meja dikosongkan
        PesananDinein::where('meja_id', $mejaId)
            ->where('status', 'menunggu_pembayaran')
            ->update(['status' => 'batal']);

        $meja->update(['status' => 'kosong']);
        return redirect()->back()->with('success', 'Meja ' . $meja->nomor_meja . ' berhasil dikosongkan.');
    }

    public function printDapur($pesananId)
    {
        $pesanan = PesananDinein::with('items.menu')->findOrFail($pesananId);
        return view('pos.dinein.print-dapur', compact('pesanan'));
    }

    public function printMeja($pesananId)
    {
        $pesanan = PesananDinein::with('items.menu', 'meja')->findOrFail($pesananId);
        return view('pos.dinein.print-meja', compact('pesanan'));
    }

    public function printNota($pesananId)
    {
        $pesanan = PesananDinein::with('items.menu', 'meja', 'pembayaran')->findOrFail($pesananId);
        return view('pos.dinein.print-nota', compact('pesanan'));
    }

    public function bukaShift(Request $request)
    {
        $request->validate([
            'modal_awal' => 'required|numeric|min:0'
        ]);

        $shift = \App\Models\ShiftKasir::create([
            'user_id' => auth()->id(),
            'modal_awal' => $request->modal_awal,
            'status' => 'buka',
            'dibuka_pada' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift Kasir & Modal Awal berhasil dibuka.',
            'shift' => $shift
        ]);
    }

    public function tutupShift(Request $request)
    {
        $shift = \App\Models\ShiftKasir::where('user_id', auth()->id())
            ->where('status', 'buka')
            ->first();

        if ($shift) {
            $shift->update([
                'status' => 'tutup',
                'ditutup_pada' => now(),
                'kas_akhir' => $request->kas_akhir ?? 0
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Shift Kasir berhasil ditutup.'
        ]);
    }
}
