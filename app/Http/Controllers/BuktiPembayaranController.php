<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BuktiPembayaranController extends Controller
{
    /** GET /pesan/bayar (cari pesanan untuk bayar) */
    public function cari(Request $request)
    {
        $kodePesanan = $request->query('kode_pesanan');

        if ($kodePesanan) {
            $pesanan = Pesanan::where('id_pesanan', $kodePesanan)->first();
            if ($pesanan) {
                return redirect()->route('pesanan.bayar', $kodePesanan);
            }

            return back()->with('error', 'Pesanan dengan nomor "'.$kodePesanan.'" tidak ditemukan.');
        }

        return view('admin.pos.pembayaran.cari');
    }

    /** GET /pesan/bayar/{kodePesanan} */
    public function show($kodePesanan)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengiriman', 'pembayaran', 'pelanggan'])
            ->where('id_pesanan', $kodePesanan)
            ->first();

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $type = match ($pesanan->jenis_pesanan_id) {
            2 => 'catering',
            3 => 'nasi_box',
            default => 'dine_in',
        };

        $statusBayar = $this->statusBayar($pesanan);
        if ($statusBayar === 'lunas') {
            return view('admin.pos.pembayaran.sukses', compact('pesanan', 'type', 'kodePesanan'));
        }

        return view('admin.pos.pembayaran.index', compact('pesanan', 'type', 'kodePesanan'));
    }

    /** GET /pesan/bayar/status/{kodePesanan} — polling JSON status pembayaran */
    public function statusJson($kodePesanan)
    {
        $pesanan = Pesanan::where('id_pesanan', $kodePesanan)->first();

        if (! $pesanan) {
            return response()->json([
                'lunas' => false,
                'transaction_status' => null,
                'dp_terbayar' => 0,
            ]);
        }

        $dpTerbayar = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima') // Sebagian / Lunas
            ->sum('jumlah_dibayar');
        $lunas = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima')
            ->sum('jumlah_dibayar');

        $transaksi = PaymentTransaction::where('din_number', $kodePesanan)->latest()->first();

        return response()->json([
            'lunas' => $lunas >= (float) $pesanan->total_tagihan || $dpTerbayar >= (float) $pesanan->total_tagihan,
            'transaction_status' => $transaksi?->transaction_status,
            'dp_terbayar' => $dpTerbayar,
        ]);
    }

    /** POST /pesan/bayar/simulasi-qris — instant QRIS auto-verification simulation */
    public function simulasiQris(Request $request)
    {
        $request->validate([
            'kode_pesanan' => 'required|string',
        ]);

        $pesanan = Pesanan::where('id_pesanan', $request->kode_pesanan)->first();
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $isLunasSkema = $pesanan->isSkemaLunas();
        $lunas = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $dpAmount = $pesanan->nominalDP();
        $isPelunasan = $isLunasSkema || ($lunas >= $dpAmount && $lunas < $pesanan->total_tagihan);
        $amountToPay = $isPelunasan ? max(0, $pesanan->total_tagihan - $lunas) : max(0, $dpAmount);

        // Record QRIS payment as automatically accepted/verified
        Pembayaran::create([
            'kode_pembayaran' => 'BYR-' . now()->format('Ymd-His') . '-' . rand(10, 99),
            'pesanan_id' => $pesanan->id,
            'jenis_pembayaran' => ($isLunasSkema || $isPelunasan) ? 'pelunasan' : 'uang_muka',
            'jumlah_dibayar' => $amountToPay,
            'metode_pembayaran' => 'qris',
            'status_verifikasi' => 'diterima',
            'tanggal_pembayaran' => now(),
            'catatan_verifikasi' => 'Terverifikasi otomatis via QRIS Dinamis'
        ]);

        $totalTerbayarBaru = $lunas + $amountToPay;
        $pesanan->update([
            'status_pembayaran_id' => ($totalTerbayarBaru >= (float) $pesanan->total_tagihan) ? 5 : 3,
            'status_pesanan_id' => 2
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran QRIS Dinamis berhasil terverifikasi otomatis!',
            'redirect' => route('pesanan.bayar', $pesanan->id_pesanan)
        ]);
    }

    /** POST /pesan/bukti */
    public function store(Request $request)
    {
        $request->validate([
            'kode_pesanan' => 'required|string',
            'jenis_pembayaran' => 'required|in:dp,pelunasan',
            'file_bukti' => 'required|file|mimes:jpeg,png,jpg,pdf|max:1024',
        ]);

        $pesanan = Pesanan::where('id_pesanan', $request->kode_pesanan)->first();
        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $total = (float) $pesanan->total_tagihan;
        $dpSudahBayar = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima') // Sebagian / Lunas
            ->sum('jumlah_dibayar');

        $isLunasSkema = $pesanan->isSkemaLunas();
        $isPelunasan = $isLunasSkema || $request->jenis_pembayaran === 'pelunasan';

        $dpPersentase = $pesanan->jenis_pesanan_id == 3 ? 0.25 : 0.5; // Nasi Box = 25%, Catering = 50%

        $jumlahBayar = $isPelunasan
            ? max(0, $total - $dpSudahBayar)
            : max(0, $total * $dpPersentase - $dpSudahBayar);

        if ($jumlahBayar <= 0) {
            return back()->with('error', 'Tagihan untuk pembayaran ini sudah lunas.');
        }

        $jenisBayar = $isPelunasan ? 'pelunasan' : 'uang_muka';

        $pembayaran = Pembayaran::where('pesanan_id', $pesanan->id)
            ->whereIn('jenis_pembayaran', $isPelunasan ? ['pelunasan', 'pembayaran_penuh'] : ['uang_muka'])
            ->where('status_verifikasi', 'belum_dibayar')
            ->latest()
            ->first();

        if (!$pembayaran) {
            if ($isPelunasan) {
                $pembayaran = Pembayaran::create([
                    'kode_pembayaran' => 'PAY-' . strtoupper(uniqid()),
                    'pesanan_id' => $pesanan->id,
                    'jenis_pembayaran' => 'pelunasan',
                    'metode_pembayaran' => 'transfer_bank',
                    'jumlah_dibayar' => $jumlahBayar,
                    'jumlah_tagihan' => $jumlahBayar,
                    'status_verifikasi' => 'belum_dibayar',
                    'expires_at' => now()->addHours(12),
                ]);
            } else {
                return back()->with('error', 'Sesi pembayaran tidak ditemukan atau sudah kedaluwarsa.');
            }
        }

        if ($pembayaran->expires_at && $pembayaran->expires_at < now()) {
            return back()->with('error', 'Sesi pembayaran telah berakhir. Pesanan tidak dapat dilanjutkan atau Anda harus membuat sesi baru.');
        }

        $path = $request->file('file_bukti')->store('bukti-pembayaran', 'public');

        $pembayaran->update([
            'metode_pembayaran' => 'transfer_bank',
            'status_verifikasi' => 'menunggu_verifikasi',
            'tanggal_pembayaran' => now(),
            'bukti_pembayaran' => $path,
            'catatan' => 'Bukti diunggah oleh pemesan',
        ]);

        if ($jenisBayar === 'uang_muka') {
            $pesanan->update(['status_pembayaran_id' => 2]); // Menunggu Verifikasi DP
        } else {
            $pesanan->update(['status_pembayaran_id' => 4]); // Menunggu Verifikasi Pelunasan
        }

        // Kirim notifikasi
        $admins = \App\Models\Pengguna::whereHas('peran', function ($q) {
            $q->whereIn('nama_peran', ['Pemilik', 'Admin', 'Manajer']);
        })->get();
        if ($admins->count() > 0) {
            $pesanan->loadMissing('pelanggan');
            $namaPemesan = $pesanan->pelanggan->nama ?? null;
            if (!$namaPemesan && !empty($pesanan->catatan)) {
                if (preg_match('/^Pemesan:\s*(.+)$/m', $pesanan->catatan, $m)) {
                    $namaPemesan = trim($m[1]);
                } elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $pesanan->catatan, $m)) {
                    $namaPemesan = trim($m[1]);
                } elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $pesanan->catatan, $m)) {
                    $namaPemesan = trim($m[1]);
                } else {
                    $namaPemesan = trim(explode('|', $pesanan->catatan)[0]);
                }
            }
            $atasNama = $namaPemesan ? " atas nama {$namaPemesan}" : "";
            $jenisLabel = $jenisBayar === 'uang_muka' ? 'DP' : 'Pelunasan';
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\StatusPembayaran(
                $pembayaran,
                "Pembayaran $jenisLabel Masuk",
                "Pembayaran $jenisLabel untuk pesanan #{$pesanan->id_pesanan}{$atasNama} telah diterima dan menunggu verifikasi.",
                route('admin.verifikasi_pembayaran.index')
            ));
        }

        return redirect()->route('pesanan.bayar', $pesanan->id_pesanan)
            ->with('success', 'Bukti pembayaran berhasil dikirim! Kami akan memverifikasi dalam 1×24 jam.');
    }

    /** POST /pesan/bayar/{kodePesanan}/mulai-pelunasan */
    public function mulaiSesiPelunasan(Request $request, $kodePesanan)
    {
        $pesanan = Pesanan::where('id_pesanan', $kodePesanan)->first();
        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        // Pastikan belum kedaluwarsa H-3
        if ($pesanan->batas_pelunasan && $pesanan->batas_pelunasan < now()) {
            return back()->with('error', 'Batas waktu pelunasan telah berakhir. Silakan hubungi admin.');
        }

        // Cek apakah ada sesi pelunasan yang masih aktif
        $aktif = Pembayaran::where('pesanan_id', $pesanan->id)
            ->where('jenis_pembayaran', 'pelunasan')
            ->where('status_verifikasi', 'belum_dibayar')
            ->where('expires_at', '>', now())
            ->exists();

        if ($aktif) {
            return back()->with('error', 'Anda masih memiliki sesi pelunasan yang aktif.');
        }

        $total = (float) $pesanan->total_tagihan;
        $dpSudahBayar = (float) $pesanan->pembayaran()->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $sisaBayar = max(0, $total - $dpSudahBayar);

        if ($sisaBayar <= 0) {
            return back()->with('error', 'Pesanan sudah lunas.');
        }

        Pembayaran::create([
            'kode_pembayaran' => 'PAY-' . strtoupper(uniqid()),
            'pesanan_id' => $pesanan->id,
            'jenis_pembayaran' => 'pelunasan',
            'metode_pembayaran' => null,
            'jumlah_dibayar' => $sisaBayar,
            'jumlah_tagihan' => $sisaBayar,
            'status_verifikasi' => 'belum_dibayar',
            'expires_at' => now()->addHours(12),
        ]);

        return redirect()->route('pesanan.bayar', $pesanan->id_pesanan)->with('success', 'Sesi pelunasan dimulai, selesaikan dalam 12 jam.');
    }

    /** GET /pesan/invoice/{kodePesanan} */
    public function invoicePdf(Request $request, $kodePesanan)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengiriman', 'pembayaran', 'pelanggan'])
            ->where('id_pesanan', $kodePesanan)
            ->first();

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $type = match ($pesanan->jenis_pesanan_id) {
            2 => 'catering',
            3 => 'nasi_box',
            default => 'dine_in',
        };

        $namaPemesan = optional($pesanan->pelanggan)->nama
            ?? optional($pesanan->jadwal_pesanan)->nama_penerima
            ?? \App\Models\PesananDinein::find($pesanan->id)?->nama_konsumen
            ?? '-';
        $kontak = optional($pesanan->pelanggan)->nomor_telepon
            ?? optional($pesanan->jadwal_pesanan)->nomor_telepon_penerima
            ?? '-';

        if ($request->query('preview') === '1') {
            $isPdf = false;
            return view('pelanggan.pembayaran.invoice-pdf', compact('pesanan', 'type', 'kodePesanan', 'namaPemesan', 'kontak', 'isPdf'));
        }

        $isPdf = true;
        $pdf = Pdf::loadView('pelanggan.pembayaran.invoice-pdf', compact('pesanan', 'type', 'kodePesanan', 'namaPemesan', 'kontak', 'isPdf'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Bukti-Pembayaran-' . $pesanan->id_pesanan . '.pdf');
    }

    /** Status bayar: belum_bayar / dp_terbayar / lunas */
    public function statusBayar(Pesanan $pesanan): string
    {
        $total = (float) $pesanan->total_tagihan;
        $lunas = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima')
            ->sum('jumlah_dibayar');
        $dp = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima')
            ->sum('jumlah_dibayar');

        if ($lunas >= $total || $dp >= $total) {
            return 'lunas';
        }
        if ($dp > 0) {
            return 'dp_terbayar';
        }

        return 'belum_bayar';
    }
}
