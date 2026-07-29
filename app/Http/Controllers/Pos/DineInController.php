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

        // Open Bills (Pesanan Menggantung Menunggu Pembayaran)
        $openBills = PesananDinein::with(['meja', 'items.menu'])
            ->where('status', 'menunggu_pembayaran')
            ->orderBy('dibuka_pada', 'desc')
            ->get();

        // Riwayat Transaksi Lunas & Void Kasir (200 Transaksi Terakhir)
        $riwayatTransaksi = PesananDinein::with(['meja', 'items.menu', 'pembayaran.diprosesOleh', 'kasir'])
            ->orderBy('updated_at', 'desc')
            ->take(200)
            ->get();

        $cashiers = \App\Models\User::orderBy('name')->get(['id', 'name']);
        
        return view('pos.dinein.index', compact('mejas', 'menus', 'kategoris', 'openBills', 'riwayatTransaksi', 'cashiers'));
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

            $pesanan->load(['meja', 'items.menu']);

            return response()->json([
                'success' => true, 
                'message' => 'Pesanan berhasil disimpan.',
                'pesanan_id' => $pesanan->id,
                'pesanan' => $pesanan
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function clearTable($mejaId)
    {
        $meja = Meja::findOrFail($mejaId);
        
        // Cek apakah ada pesanan aktif yang belum dibayar
        $unpaidOrder = PesananDinein::where('meja_id', $mejaId)
            ->where('status', 'menunggu_pembayaran')
            ->first();

        if ($unpaidOrder) {
            return redirect()->back()->with('error', 'Meja ' . $meja->nomor_meja . ' tidak dapat dikosongkan karena masih memiliki tagihan aktif yang BELUM DIBAYAR! Silakan lakukan pembayaran terlebih dahulu.');
        }

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

    public function printGabungan($pesananId)
    {
        $pesanan = PesananDinein::with('items.menu', 'meja')->findOrFail($pesananId);
        return view('pos.dinein.print-gabungan', compact('pesanan'));
    }

    public function printNota($pesananId)
    {
        $pesanan = PesananDinein::with('items.menu', 'meja', 'pembayaran')->findOrFail($pesananId);
        return view('pos.dinein.print-nota', compact('pesanan'));
    }

    public function printQrMeja()
    {
        $mejas = Meja::orderBy('id', 'asc')->get();
        return view('pos.dinein.qr-meja', compact('mejas'));
    }

    public function updateSubStatus(Request $request, $pesananId)
    {
        $request->validate([
            'sub_status' => 'required|string|in:diproses,siap_diantar,sudah_diantar,menunggu_bayar'
        ]);

        $pesanan = PesananDinein::findOrFail($pesananId);
        $pesanan->update(['sub_status' => $request->sub_status]);

        return response()->json([
            'success' => true,
            'message' => 'Sub-status progress pesanan berhasil diperbarui.',
            'sub_status' => $pesanan->sub_status
        ]);
    }

    public function toggleStatusSajian($itemId)
    {
        $item = \App\Models\ItemPesananDinein::findOrFail($itemId);
        $item->update(['status_sajian' => !$item->status_sajian]);

        return response()->json([
            'success' => true,
            'message' => 'Status sajian item berhasil diperbarui.',
            'status_sajian' => $item->status_sajian
        ]);
    }

    public function voidOrder(Request $request, $pesananId)
    {
        $request->validate([
            'alasan_void' => 'required|string',
            'catatan' => 'nullable|string'
        ]);

        $pesanan = PesananDinein::with('meja', 'pembayaran')->findOrFail($pesananId);

        if ($pesanan->status === 'void') {
            return response()->json(['success' => false, 'message' => 'Pesanan ini sudah dibatalkan/void.'], 400);
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($pesanan, $request) {
            $pesanan->update([
                'status' => 'void',
                'sub_status' => 'batal'
            ]);

            if ($pesanan->pembayaran) {
                $pesanan->pembayaran->update([
                    'status' => 'void'
                ]);
            }

            if ($pesanan->meja) {
                $pesanan->meja->update(['status' => 'kosong']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Pesanan #' . ($pesanan->kode_pesanan ?? ('DIN-' . $pesanan->id)) . ' berhasil dibatalkan (Void).'
        ]);
    }

    public function toggleMenuStatus($menuId)
    {
        $menu = Menu::findOrFail($menuId);
        $newStatus = ($menu->status === 'aktif' || $menu->status === 'tersedia') ? 'habis' : 'aktif';
        $menu->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Status menu ' . $menu->nama . ' berhasil diubah menjadi ' . strtoupper($newStatus) . '.',
            'status' => $newStatus,
            'is_habis' => $menu->isHabis()
        ]);
    }
}
