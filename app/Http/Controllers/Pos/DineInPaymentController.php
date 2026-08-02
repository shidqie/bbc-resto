<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\OrderService;
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
            ->where('status_pesanan_id', 1) // 1 = Menunggu Pembayaran
            ->where('jenis_pesanan_id', 1) // Dine in
            ->first();

        if (! $pesanan) {
            $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja'])
                ->where('meja_id', $id)
                ->where('status_pesanan_id', 1)
                ->where('jenis_pesanan_id', 1)
                ->latest()
                ->first();
        }

        if (! $pesanan) {
            return redirect()->route('pos.dinein.index')->with('error', 'Pesanan tidak ditemukan atau tagihan sudah lunas.');
        }

        $meja = $pesanan->meja;
        $totalTagihan = $pesanan->total_tagihan;

        return view('pos.pesanan.checkout', compact('meja', 'pesanan', 'totalTagihan'));
    }

    public function processPayment(Request $request, $mejaId)
    {
        $request->validate([
            'pesanan_id' => 'required|exists:pesanan,id',
            'metode_bayar' => 'required|string', // cash or nontunai
            'total_tagihan' => 'required|numeric',
            'jumlah_bayar' => 'nullable|numeric', // For cash
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Pesanan::findOrFail($request->pesanan_id);
            $metodeId = $request->metode_bayar === 'nontunai' ? 2 : 1; // 2 = QRIS, 1 = Tunai

            // 1. Create pembayaran
            Pembayaran::create([
                'nomor_pembayaran' => 'PAY-'.date('YmdHis').'-'.rand(100, 999),
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran_id' => $metodeId,
                'jenis_pembayaran_id' => 1, // 1 = Pembayaran Penuh
                'jumlah_bayar' => $request->jumlah_bayar ?? $pesanan->total_tagihan,
                'status_pembayaran_id' => 2, // 2 = Lunas
                'diproses_oleh' => Auth::id(),
                'dibayar_pada' => now(),
            ]);

            // 2. Potong stok via OrderService (harus dipanggil sebelum status diupdate jadi selesai)
            app(OrderService::class)->completeOrder($pesanan);

            // 3. Update status pesanan & meja
            $pesanan->update([
                'status_pesanan_id' => 5, // 5 = Selesai
            ]);

            if ($pesanan->meja) {
                $pesanan->meja->update(['status_meja_id' => 1]); // 1 = Tersedia
            }

            DB::commit();

            return redirect()->route('pos.dinein.success', $request->pesanan_id)
                ->with('success', 'Pembayaran berhasil! Silakan cetak nota.');
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

        return view('pos.pesanan.success', compact('pesanan'));
    }

    public function receipts($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pembayaran'])->findOrFail($pesananId);

        return view('pos.dinein.receipts', compact('pesanan'));
    }
}
