<?php

namespace App\Http\Controllers;

use App\Models\PaketCatering;
use App\Models\PesananCatering;
use App\Models\PembayaranCatering;
use App\Models\DetailPesananCatering;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PesananCateringController extends Controller
{
    /**
     * Daftar pesanan catering (Admin/Manajer)
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $query = PesananCatering::with('paketCatering')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $pesanans = $query->get();
        return view('catering.pesanan.index', compact('pesanans', 'status'));
    }

    /**
     * Form pemesanan publik (tanpa login / guest)
     */
    public function createPublic()
    {
        $pakets = PaketCatering::where('is_active', true)->get();
        return view('catering.pesanan.create-public', compact('pakets'));
    }

    /**
     * Simpan pesanan dari form publik
     */
    public function storePublic(Request $request)
    {
        $request->validate([
            'paket_catering_id' => 'required|exists:paket_caterings,id',
            'nama_pemesan' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:20',
            'email' => 'nullable|email',
            'alamat_pengiriman' => 'required|string',
            'tanggal_acara' => 'required|date',
            'detail_acara' => 'nullable|string',
            'jumlah_porsi' => 'required|integer|min:1',
        ]);

        $paket = PaketCatering::findOrFail($request->paket_catering_id);

        // Validasi tanggal acara
        $tanggalAcara = Carbon::parse($request->tanggal_acara);
        $today = Carbon::today();

        if ($paket->jenis_paket === 'catering') {
            // Catering: minimal H+14
            $minDate = $today->copy()->addDays(14);
            if ($tanggalAcara->lt($minDate)) {
                return back()->withErrors(['tanggal_acara' => 'Pemesanan catering minimal 14 hari sebelum acara.'])->withInput();
            }
        } else {
            // Nasi Box: minimal H+2
            $minDate = $today->copy()->addDays(2);
            if ($tanggalAcara->lt($minDate)) {
                return back()->withErrors(['tanggal_acara' => 'Pemesanan nasi box minimal 2 hari sebelum acara.'])->withInput();
            }
        }

        $totalHarga = $paket->harga * $request->jumlah_porsi;
        $dpPercentage = $paket->jenis_paket === 'catering' ? 50 : 25;
        $dpAmount = $totalHarga * ($dpPercentage / 100);

        $pesanan = PesananCatering::create([
            'no_pesanan' => PesananCatering::generateNoPesanan($paket->jenis_paket),
            'paket_catering_id' => $paket->id,
            'nama_pemesan' => $request->nama_pemesan,
            'no_telepon' => $request->no_telepon,
            'email' => $request->email,
            'alamat_pengiriman' => $request->alamat_pengiriman,
            'tanggal_acara' => $request->tanggal_acara,
            'detail_acara' => $request->detail_acara,
            'jumlah_porsi' => $request->jumlah_porsi,
            'harga_per_porsi' => $paket->harga,
            'total_harga' => $totalHarga,
            'dp_amount' => $dpAmount,
            'dp_percentage' => $dpPercentage,
            'sisa_pembayaran' => $totalHarga,
            'status' => 'menunggu_konfirmasi',
        ]);

        return redirect()->route('catering.pesanan.success', $pesanan->id)
            ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran DP.');
    }

    /**
     * Halaman sukses setelah pesan
     */
    public function success(PesananCatering $pesananCatering)
    {
        $pesananCatering->load('paketCatering');
        return view('catering.pesanan.success', compact('pesananCatering'));
    }

    /**
     * Detail pesanan (Admin)
     */
    public function show(PesananCatering $pesananCatering)
    {
        $pesananCatering->load(['paketCatering.detailBahan.bahanBaku.satuan', 'pembayarans.verifiedBy', 'confirmedBy']);
        return view('catering.pesanan.show', compact('pesananCatering'));
    }

    /**
     * Konfirmasi pesanan oleh Admin/Pemilik
     */
    public function confirm(PesananCatering $pesananCatering)
    {
        if ($pesananCatering->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Pesanan tidak dapat dikonfirmasi.');
        }

        // Cek apakah sudah lewat H-3
        $batasKonfirmasi = Carbon::parse($pesananCatering->tanggal_acara)->subDays(3);
        if (Carbon::today()->gt($batasKonfirmasi)) {
            return back()->with('error', 'Sudah melewati batas H-3. Pesanan tidak dapat dikonfirmasi.');
        }

        DB::transaction(function () use ($pesananCatering) {
            $stockService = new StockService();
            $paket = $pesananCatering->paketCatering()->with('detailBahan.bahanBaku')->first();

            // Potong stok sesuai BOM x jumlah porsi
            foreach ($paket->detailBahan as $bom) {
                $jumlahTotal = $bom->jumlah_kebutuhan * $pesananCatering->jumlah_porsi;

                $stockService->deductStock(
                    $bom->bahan_baku_id,
                    $jumlahTotal,
                    'Pesanan Catering #' . $pesananCatering->no_pesanan
                );

                DetailPesananCatering::create([
                    'pesanan_catering_id' => $pesananCatering->id,
                    'bahan_baku_id' => $bom->bahan_baku_id,
                    'jumlah_digunakan' => $jumlahTotal,
                ]);
            }

            $pesananCatering->update([
                'status' => 'terkonfirmasi',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);
        });

        return back()->with('success', 'Pesanan berhasil dikonfirmasi dan stok telah dipotong.');
    }

    /**
     * Upload bukti pembayaran
     */
    public function uploadBukti(Request $request, PesananCatering $pesananCatering)
    {
        $request->validate([
            'jenis_pembayaran' => 'required|in:dp,pelunasan',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode' => 'required|in:cash,transfer,qris',
            'bukti_bayar' => 'nullable|image|max:2048',
            'catatan' => 'nullable|string',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiPath = $request->file('bukti_bayar')->store('bukti-bayar-catering', 'public');
        }

        PembayaranCatering::create([
            'pesanan_catering_id' => $pesananCatering->id,
            'jenis_pembayaran' => $request->jenis_pembayaran,
            'jumlah_bayar' => $request->jumlah_bayar,
            'metode' => $request->metode,
            'bukti_bayar' => $buktiPath,
            'status' => $request->metode === 'cash' ? 'verified' : 'pending',
            'verified_by' => $request->metode === 'cash' ? auth()->id() : null,
            'verified_at' => $request->metode === 'cash' ? now() : null,
            'catatan' => $request->catatan,
        ]);

        // Jika cash, langsung update sisa pembayaran
        if ($request->metode === 'cash') {
            $totalBayar = $pesananCatering->pembayarans()->where('status', 'verified')->sum('jumlah_bayar') + $request->jumlah_bayar;
            $sisa = $pesananCatering->total_harga - $totalBayar;
            $pesananCatering->update(['sisa_pembayaran' => max(0, $sisa)]);

            if ($sisa <= 0) {
                $pesananCatering->update(['status' => 'lunas']);
            }
        }

        return back()->with('success', 'Pembayaran berhasil dicatat!');
    }

    /**
     * Verifikasi pembayaran transfer (Admin)
     */
    public function verifyPembayaran(PembayaranCatering $pembayaran, Request $request)
    {
        $request->validate(['action' => 'required|in:verify,reject']);

        if ($request->action === 'verify') {
            $pembayaran->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // Update sisa pembayaran
            $pesanan = $pembayaran->pesananCatering;
            $totalBayar = $pesanan->pembayarans()->where('status', 'verified')->sum('jumlah_bayar');
            $sisa = $pesanan->total_harga - $totalBayar;
            $pesanan->update(['sisa_pembayaran' => max(0, $sisa)]);

            if ($sisa <= 0) {
                $pesanan->update(['status' => 'lunas']);
            }
        } else {
            $pembayaran->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
        }

        return back()->with('success', 'Pembayaran ' . ($request->action === 'verify' ? 'diverifikasi' : 'ditolak') . '!');
    }

    /**
     * Batalkan pesanan
     */
    public function cancel(Request $request, PesananCatering $pesananCatering)
    {
        $request->validate(['catatan_pembatalan' => 'required|string']);

        $pesananCatering->update([
            'status' => 'dibatalkan',
            'catatan_pembatalan' => $request->catatan_pembatalan,
        ]);

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    /**
     * Tandai pesanan selesai
     */
    public function complete(PesananCatering $pesananCatering)
    {
        $pesananCatering->update(['status' => 'selesai']);
        return back()->with('success', 'Pesanan ditandai selesai!');
    }
}
