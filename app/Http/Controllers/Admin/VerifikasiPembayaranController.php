<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifikasiPembayaranController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'menunggu_verifikasi');
        $search = $request->query('search');

        $query = Pembayaran::with(['pesanan.detail_pesanan'])
            ->whereNotNull('bukti_pembayaran');

        if ($status === 'menunggu_verifikasi') {
            $query->where('status_verifikasi', 'menunggu_verifikasi');
        } elseif ($status === 'riwayat') {
            $query->whereIn('status_verifikasi', ['diterima', 'ditolak']);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_pembayaran', 'like', "%{$search}%")
                    ->orWhereHas('pesanan', function ($pq) use ($search) {
                        $pq->where('nomor_pesanan', 'like', "%{$search}%");
                    });
            });
        }

        $pembayarans = $query->latest()->paginate(20)->withQueryString();

        return view('admin.pembayaran.verifikasi', compact('pembayarans', 'status'));
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:terima,tolak',
            'catatan' => 'required_if:action,tolak|nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $pembayaran = Pembayaran::with('pesanan')->findOrFail($id);

            if ($pembayaran->status_verifikasi !== 'menunggu_verifikasi') {
                return back()->with('error', 'Status pembayaran tidak valid untuk diverifikasi.');
            }

            if ($request->action === 'terima') {
                $pembayaran->update([
                    'status_verifikasi' => 'diterima',
                    'diverifikasi_oleh' => Auth::id(),
                    'tanggal_verifikasi' => now(),
                    'catatan_verifikasi' => $request->catatan,
                ]);

                // Update pesanan status
                $pesanan = $pembayaran->pesanan;
                
                // If it's a Dine In order (jenis_pesanan_id = 1)
                if ($pesanan->jenis_pesanan_id == 1) {
                    app(OrderService::class)->completeOrder($pesanan);
                    $pesanan->update(['status_pesanan_id' => 5]); // Selesai
                    if ($pesanan->meja) {
                        $pesanan->meja->update(['status_meja_id' => 1]); // Tersedia
                    }
                } 
                // For Catering/Nasi Box DP (jenis_pesanan_id = 2 or 3)
                else {
                    app(OrderService::class)->potongStokPesanan($pesanan);
                    
                    if ($pembayaran->jenis_pembayaran === 'uang_muka') {
                        $pesanan->update(['status_pesanan_id' => 6]); // Example: DP Dibayar (Adjust status ID as needed)
                    } elseif ($pembayaran->jenis_pembayaran === 'pelunasan' || $pembayaran->jenis_pembayaran === 'pembayaran_penuh') {
                        $pesanan->update(['status_pesanan_id' => 8]); // Example: Lunas
                    }
                }

                $msg = 'Pembayaran berhasil diterima dan diverifikasi.';
            } else {
                $pembayaran->update([
                    'status_verifikasi' => 'ditolak',
                    'diverifikasi_oleh' => Auth::id(),
                    'tanggal_verifikasi' => now(),
                    'catatan_verifikasi' => $request->catatan,
                ]);

                $msg = 'Pembayaran ditolak.';
            }

            DB::commit();

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
