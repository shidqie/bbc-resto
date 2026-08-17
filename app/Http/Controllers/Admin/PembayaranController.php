<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with([
            'pesanan.jenis_pesanan', 
            'pesanan.pelanggan', 
            'pesanan.meja', 
            'pesanan.jadwal_pesanan',
            'jenis_pembayaran', 
            'status_pembayaran',
            'diverifikasi_oleh_pengguna'
        ])->orderBy('dibuat_pada', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('pesanan', function($qP) use ($search) {
                      $qP->where('id_pesanan', 'like', "%{$search}%");
                  });
            });
        }

        $pembayarans = $query->paginate(20)->withQueryString();

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    public function show($id)
    {
        $pembayaran = Pembayaran::with([
            'pesanan.jenis_pesanan',
            'pesanan.pelanggan',
            'diverifikasi_oleh_pengguna'
        ])->findOrFail($id);

        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    public function verify(Request $request, $id)
    {
        $pembayaran = Pembayaran::with('pesanan')->findOrFail($id);

        DB::beginTransaction();
        try {
            $pembayaran->update([
                'status_verifikasi' => 'diterima',
                'diverifikasi_oleh' => Auth::id(),
                'tanggal_verifikasi' => now(),
            ]);

            if ($pembayaran->pesanan && $pembayaran->pesanan->status_pesanan_id != 5) {
                if ($pembayaran->pesanan->jenis_pesanan_id == 1) {
                    try {
                        app(\App\Services\OrderService::class)->potongStokPesanan($pembayaran->pesanan);
                    } catch (\RuntimeException $e) {
                        return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage() . ' Silakan tambah stok bahan terlebih dahulu.');
                    }
                    $pembayaran->pesanan->update(['status_pesanan_id' => 5]);
                    if ($pembayaran->pesanan->meja) {
                        $pembayaran->pesanan->meja->update(['status_meja_id' => 1]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal verifikasi: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $pembayaran = Pembayaran::with('pesanan')->findOrFail($id);

        DB::beginTransaction();
        try {
            $pembayaran->update([
                'status_verifikasi' => 'ditolak',
                'catatan_verifikasi' => $request->input('alasan', 'Dibatalkan oleh admin.'),
                'diverifikasi_oleh' => Auth::id(),
                'tanggal_verifikasi' => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Pesanan berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan: ' . $e->getMessage());
        }
    }
}
