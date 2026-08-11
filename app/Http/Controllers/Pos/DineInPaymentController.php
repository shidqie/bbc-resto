<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\OrderService;
use App\Helpers\QrisHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DineInPaymentController extends Controller
{
    public function checkout($id)
    {
        // 1. Cari pesanan aktif langsung berdasarkan ID Pesanan atau ID Meja
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja'])
            ->where('id', $id)
            ->whereNotIn('status_pesanan_id', [5, 6]) // Exclude Selesai & Dibatalkan
            ->where('jenis_pesanan_id', 1) // Dine in
            ->first();

        if (! $pesanan) {
            $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja'])
                ->where('meja_id', $id)
                ->whereNotIn('status_pesanan_id', [5, 6])
                ->where('jenis_pesanan_id', 1)
                ->latest()
                ->first();
        }

        if (! $pesanan) {
            return redirect()->route('pos.dinein.index')->with('error', 'Pesanan tidak ditemukan atau tagihan sudah lunas.');
        }

        $meja = $pesanan->meja;
        $totalTagihan = $pesanan->total_tagihan;

        // Base Static QRIS dari restoran
        $staticQris = "00020101021126690021ID.CO.BANKMANDIRI.WWW01189360000801988998370211719889983700303UMI51440014ID.CO.QRIS.WWW0215ID10264761295010303UMI5204581253033605802ID5915Rumah Makan BBC6015Bandung Barat (61054055162070703A016304AC4D";
        
        // Generate Dynamic QRIS string
        $qrisString = QrisHelper::generateDynamicQris($staticQris, $totalTagihan);

        return view('admin.pos.pesanan.checkout', compact('meja', 'pesanan', 'totalTagihan', 'qrisString'));
    }

    public function processPayment(Request $request, $mejaId)
    {
        $request->validate([
            'pesanan_id' => 'required|exists:pesanan,id',
            'metode_bayar' => 'required|string|in:tunai,transfer_bank,qris_manual',
            'total_tagihan' => 'required|numeric',
            'jumlah_bayar' => 'nullable|numeric',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Pesanan::findOrFail($request->pesanan_id);
            $metode = $request->metode_bayar;
            
            $kodePembayaran = 'PAY-'.date('YmdHis').'-'.rand(100, 999);
            $buktiPath = null;
            
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $ext = $file->getClientOriginalExtension();
                $filename = 'BUKTI-'.$kodePembayaran.'-'.time().'.'.$ext;
                $buktiPath = $file->storeAs('bukti-pembayaran', $filename, 'public');
            }

            // Pembayaran via Kasir POS otomatis terverifikasi
            $statusVerifikasi = 'diterima';
            $diverifikasiOleh = Auth::id();
            $tanggalVerifikasi = now();

            $pembayaran = Pembayaran::create([
                'kode_pembayaran' => $kodePembayaran,
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran' => $metode,
                'jenis_pembayaran' => 'pembayaran_penuh',
                'jumlah_tagihan' => $pesanan->total_tagihan,
                'jumlah_dibayar' => $request->jumlah_bayar ?? $pesanan->total_tagihan,
                'status_verifikasi' => $statusVerifikasi,
                'tanggal_pembayaran' => now(),
                'diverifikasi_oleh' => $diverifikasiOleh,
                'tanggal_verifikasi' => $tanggalVerifikasi,
                'bukti_pembayaran' => $buktiPath,
            ]);

            if ($statusVerifikasi === 'diterima') {
                // Potong stok via OrderService
                app(OrderService::class)->completeOrder($pesanan);

                // Update status pesanan & meja
                $pesanan->update(['status_pesanan_id' => 5]); // Selesai

                if ($pesanan->meja) {
                    $pesanan->meja->update(['status_meja_id' => 1]); // Tersedia
                }
            } else {
                // Keep order open but note it's waiting for verification
                // Or you can set a specific status if needed. 
                // For now, let it be Menunggu Pembayaran (or a new status)
            }

            DB::commit();

            if ($statusVerifikasi === 'diterima') {
                return redirect()->route('pos.dinein.success', $request->pesanan_id)
                    ->with('success', 'Pembayaran berhasil! Silakan cetak nota.');
            } else {
                return redirect()->route('pos.dinein.index')
                    ->with('success', 'Pembayaran sedang menunggu verifikasi.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function success($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pembayaran'])->findOrFail($pesananId);

        if ($pesanan->status_pesanan_id !== 5) {
            return redirect()->route('pos.dinein.index')->with('error', 'Pesanan belum lunas.');
        }

        return view('admin.pos.pesanan.success', compact('pesanan'));
    }

    public function receipts($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pembayaran'])->findOrFail($pesananId);

        return view('admin.pos.dinein.receipts', compact('pesanan'));
    }


}
