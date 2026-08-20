<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.pesanan.index');
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
            $oldStatusId = $pesanan->status_pesanan_id;
            
            if ($request->status_pesanan == 'selesai' && $pesanan->status_pesanan_id != 5) {
                $orderService->completeOrder($pesanan);
                $pesanan->update(['status_pesanan_id' => 5]);
            } elseif ($request->status_pesanan == 'dibatalkan' && $pesanan->status_pesanan_id != 6) {
                $pesanan->update(['status_pesanan_id' => 6]);
            } else {
                $map = ['baru' => 1, 'diproses' => 3, 'dikirim' => 4, 'selesai' => 5, 'dibatalkan' => 6];
                if (isset($map[$request->status_pesanan])) {
                    $pesanan->update(['status_pesanan_id' => $map[$request->status_pesanan]]);
                }
            }

            // Kirim Notifikasi Status
            if ($oldStatusId != $pesanan->status_pesanan_id) {
                $status = $pesanan->status_pesanan_id;
                $statusTexts = [
                    1 => 'menunggu konfirmasi',
                    2 => 'telah dikonfirmasi',
                    3 => 'sedang diproses',
                    4 => 'dalam pengantaran',
                    5 => 'telah selesai',
                    6 => 'dibatalkan',
                ];
                $statusText = $statusTexts[$status] ?? 'diperbarui';

                // 1. Notifikasi ke Konsumen
                if ($pesanan->pelanggan) {
                    $pesanan->pelanggan->notify(new \App\Notifications\StatusPesanan(
                        'Status Pesanan Diperbarui', 
                        "Pesanan {$pesanan->id_pesanan} Anda {$statusText}.", 
                        route('konsumen.pesanan.index')
                    ));
                }

                // 2. Notifikasi ke Dapur & Pemilik (Dikonfirmasi / Diproses)
                if (in_array($status, [2, 3])) {
                    $roles = $status == 2 ? ['Dapur'] : ['Dapur', 'Pemilik'];
                    $internalMsg = $status == 2 
                        ? "Pesanan {$pesanan->id_pesanan} telah dikonfirmasi. Silakan cek detail." 
                        : "Pesanan {$pesanan->id_pesanan} siap diproses (produksi bisa dimulai).";
                    
                    $internals = \App\Models\Pengguna::whereHas('peran', function ($q) use ($roles) {
                        $q->whereIn('nama_peran', $roles);
                    })->get();
                    
                    if ($internals->count() > 0) {
                        \Illuminate\Support\Facades\Notification::send($internals, new \App\Notifications\StatusPesanan(
                            'Update Pesanan',
                            $internalMsg,
                            route('admin.pesanan.index')
                        ));
                    }
                }

                // 3. Notifikasi ke Tim Pengantaran (Siap Dikirim)
                if ($status == 4) {
                    $kurirs = \App\Models\Pengguna::whereHas('peran', function ($q) {
                        $q->where('nama_peran', 'Tim Pengantaran');
                    })->get();
                    if ($kurirs->count() > 0) {
                        \Illuminate\Support\Facades\Notification::send($kurirs, new \App\Notifications\StatusPesanan(
                            'Pesanan Siap Dikirim',
                            "Pesanan {$pesanan->id_pesanan} siap dikirim.",
                            route('admin.pengantaran.index')
                        ));
                    }
                }

                // 4. Notifikasi ke Pemilik (Selesai)
                if ($status == 5) {
                    $pemilik = \App\Models\Pengguna::whereHas('peran', function ($q) {
                        $q->where('nama_peran', 'Pemilik');
                    })->get();
                    if ($pemilik->count() > 0) {
                        \Illuminate\Support\Facades\Notification::send($pemilik, new \App\Notifications\StatusPesanan(
                            'Pesanan Selesai',
                            "Pesanan {$pesanan->id_pesanan} telah selesai / pengiriman selesai.",
                            route('admin.pesanan.index')
                        ));
                    }
                }
            }

            return back()->with('success', 'Status pesanan diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
