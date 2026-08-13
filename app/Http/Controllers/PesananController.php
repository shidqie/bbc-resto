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

            // Send Notification to Customer if status changed
            if ($oldStatusId != $pesanan->status_pesanan_id && $pesanan->pelanggan) {
                $statusTexts = [
                    1 => 'menunggu konfirmasi',
                    2 => 'telah dikonfirmasi',
                    3 => 'sedang diproses',
                    4 => 'dalam pengantaran',
                    5 => 'telah selesai',
                    6 => 'dibatalkan',
                ];
                $statusText = $statusTexts[$pesanan->status_pesanan_id] ?? 'diperbarui';
                $pesanan->pelanggan->notify(new \App\Notifications\StatusPesanan(
                    'Status Pesanan Diperbarui', 
                    'Pesanan ' . $pesanan->kode_pesanan . ' Anda ' . $statusText . '.', 
                    route('konsumen.pesanan.index')
                ));
            }

            return back()->with('success', 'Status pesanan diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
