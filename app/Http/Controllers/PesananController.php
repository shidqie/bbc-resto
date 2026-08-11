<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.pesanan.index');
    }

    public function create()
    {
        $menus = Menu::with('kategori')->where('status', 'tersedia')->get();

        return view('pesanan.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'nullable|string|max:255',
            'no_meja' => 'nullable|string|max:50',
            'jenis_pesanan' => 'required|in:dine_in,take_away,catering,nasi_box',
            'tanggal_pengiriman' => 'nullable|date',
            'menu_id' => 'required|array',
            'menu_id.*' => 'required|exists:menu,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|integer|min:1',
            'catatan' => 'nullable|array',
            'jumlah_bayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:tunai,transfer,qris',
        ]);

        try {
            DB::beginTransaction();

            // Generate No Pesanan
            $lastPesanan = Pesanan::latest()->first();
            $lastId = $lastPesanan ? $lastPesanan->id : 0;
            $noPesanan = 'INV-'.date('Ymd').'-'.str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $pesanan = Pesanan::create([
                'no_pesanan' => $noPesanan,
                'nama_pelanggan' => $request->nama_pelanggan,
                'no_meja' => $request->no_meja,
                'jenis_pesanan' => $request->jenis_pesanan,
                'tanggal_pesanan' => now(),
                'tanggal_pengiriman' => $request->tanggal_pengiriman,
                'jumlah_porsi' => 1, // default 1 (atau total item, disesuaikan)
                'status_pesanan' => 'baru',
                'user_id' => Auth::id(),
                'total_harga' => 0,
            ]);

            $totalHarga = 0;
            foreach ($request->menu_id as $index => $menuId) {
                $menu = Menu::find($menuId);
                $qty = $request->jumlah[$index];
                $subtotal = $qty * $menu->harga;
                $totalHarga += $subtotal;

                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $menu->id,
                    'jumlah' => $qty,
                    'harga_satuan' => $menu->harga,
                    'subtotal' => $subtotal,
                    'catatan' => $request->catatan[$index] ?? null,
                ]);
            }

            // Hitung status pembayaran
            $statusBayar = 'belum_bayar';
            $jenisPembayaran = 'full';
            if ($request->jumlah_bayar > 0 && $request->jumlah_bayar < $totalHarga) {
                $statusBayar = 'dp';
                $jenisPembayaran = 'dp';
            } elseif ($request->jumlah_bayar >= $totalHarga) {
                $statusBayar = 'lunas';
                $jenisPembayaran = 'full';
            }

            $pesanan->update([
                'total_harga' => $totalHarga,
                'status_pembayaran' => $statusBayar,
            ]);

            if ($request->jumlah_bayar > 0) {
                Pembayaran::create([
                    'pesanan_id' => $pesanan->id,
                    'jumlah_bayar' => $request->jumlah_bayar,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'jenis_pembayaran' => $jenisPembayaran,
                    'tanggal_bayar' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('pesanan.show', $pesanan->id)->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function show(Pesanan $pesanan)
    {
        $pesanan->load(['details.menu', 'pembayarans', 'user']);

        return view('admin.pos.pesanan.show', compact('pesanan'));
    }

    public function updateStatus(Request $request, Pesanan $pesanan, OrderService $orderService)
    {
        $request->validate([
            'status_pesanan' => 'required|in:baru,diproses,selesai,dibatalkan,dikirim',
        ]);

        try {
            // Note: $request->status_pesanan string is legacy. Assuming it's the old schema,
            // but just in case, use the ID mapping or keep as is.
            if ($request->status_pesanan == 'selesai' && $pesanan->status_pesanan_id != 5) {
                $orderService->completeOrder($pesanan);
                $pesanan->update(['status_pesanan_id' => 5]);
            } elseif ($request->status_pesanan == 'dibatalkan' && $pesanan->status_pesanan_id != 6) {
                // $orderService->cancelOrder($pesanan);
                $pesanan->update(['status_pesanan_id' => 6]);
            } else {
                // Legacy map
                $map = ['baru' => 1, 'diproses' => 3, 'dikirim' => 4, 'selesai' => 5, 'dibatalkan' => 6];
                if (isset($map[$request->status_pesanan])) {
                    $pesanan->update(['status_pesanan_id' => $map[$request->status_pesanan]]);
                }
            }

            return back()->with('success', 'Status pesanan diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cetak(Pesanan $pesanan, $type)
    {
        if (! in_array($type, ['konsumen', 'dapur', 'meja'])) {
            abort(404);
        }

        $pesanan->load(['details.menu', 'pembayarans', 'user']);

        return view("pesanan.print.{$type}", compact('pesanan'));
    }
}
