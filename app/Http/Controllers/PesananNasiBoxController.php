<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananNasiBoxRequest;
use App\Models\PaketCatering;
use App\Models\PesananNasiBox;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Services\PesananNasiBoxService;
use Illuminate\Http\Request;

class PesananNasiBoxController extends Controller
{
    // ─── PUBLIC ──────────────────────────────────────────────────────────────

    /** GET /pesan/nasi-box */
    public function create()
    {
        $pakets = PaketCatering::with('komponens.opsi.menu')
            ->where('is_active', true)
            ->where('jenis_paket', 'nasi_box')
            ->get();
        return view('pesanan.nasibox.create', compact('pakets'));
    }

    /** POST /pesan/nasi-box/preview (AJAX) */
    public function preview(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:paket_caterings,id',
            'jumlah_box' => 'required|integer|min:10',
            'metode_pengiriman' => 'required|in:pickup,delivery',
            'jarak_km' => 'required_if:metode_pengiriman,delivery|nullable|numeric|min:0'
        ]);
        
        try {
            $ongkir = PesananNasiBoxService::hitungOngkir($request->jumlah_box, $request->jarak_km, $request->metode_pengiriman);
            $result = PesananNasiBoxService::hitungTotal($request->paket_id, $request->jumlah_box, $ongkir);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** POST /pesan/nasi-box */
    public function store(StorePesananNasiBoxRequest $request)
    {
        $validated = $request->validated();

        try {
            $ongkir = PesananNasiBoxService::hitungOngkir($validated['jumlah_box'], $validated['jarak_km'] ?? null, $validated['metode_pengiriman']);
            $hitung = PesananNasiBoxService::hitungTotal($validated['paket_id'], $validated['jumlah_box'], $ongkir);
        } catch (\Exception $e) {
            return back()->withErrors(['metode_pengiriman' => $e->getMessage()])->withInput();
        }

        $pesanan = PesananNasiBox::create([
            'kode_pesanan'  => PesananNasiBox::generateKodePesanan(),
            'nama_pemesan'  => $validated['nama_pemesan'],
            'kontak'        => $validated['kontak'],
            'alamat'        => $validated['alamat'],
            'metode_pengiriman' => $validated['metode_pengiriman'],
            'ongkos_kirim'  => $hitung['ongkir'],
            'jarak_km'      => $validated['jarak_km'] ?? null,
            'latitude'      => $validated['latitude'] ?? null,
            'longitude'     => $validated['longitude'] ?? null,
            'tanggal_acara' => $validated['tanggal_acara'],
            'paket_id'      => $validated['paket_id'],
            'jumlah_box'    => $validated['jumlah_box'],
            'total_tagihan' => $hitung['total'],
            'dp_amount'     => $validated['opsi_pembayaran'] === 'lunas' ? $hitung['total'] : $hitung['dp'],
            'status'        => 'ditinjau',
            'status_bayar'  => 'belum_bayar',
            'catatan'       => $validated['catatan'] ?? null,
            'user_id'       => \Illuminate\Support\Facades\Auth::id() ?? null,
        ]);

        foreach ($validated['komponen'] as $komponenId => $menuId) {
            \App\Models\PesananNasiBoxDetail::create([
                'pesanan_nasi_box_id' => $pesanan->id,
                'komponen_paket_id'   => $komponenId,
                'menu_id'             => $menuId
            ]);
        }


        // Generate Midtrans Snap Token
        \App\Http\Controllers\MidtransController::generateSnapToken($pesanan, 'nasi_box');

        return redirect()->route('pesanan.bayar', $pesanan->kode_pesanan)
            ->with('success', 'Pesanan berhasil dibuat! Silakan lanjutkan ke pembayaran DP.');
    }

    // ─── ADMIN ───────────────────────────────────────────────────────────────

    /** GET /admin/pesanan/nasi-box */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = PesananNasiBox::with('paket')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_pesanan', 'like', "%{$search}%")
                  ->orWhere('nama_pemesan', 'like', "%{$search}%");
            });
        }

        $pesanans = $query->paginate(10)->withQueryString();

        $stats = [
            'baru'     => PesananNasiBox::where('status', 'ditinjau')->count(),
            'diproses' => PesananNasiBox::whereIn('status', ['dikonfirmasi', 'menunggu_pelunasan', 'diproses', 'menunggu_pengiriman', 'dikirim'])->count(),
            'selesai'  => PesananNasiBox::where('status', 'selesai')->count(),
        ];

        return view('admin.pesanan.nasibox.index', compact('pesanans', 'status', 'stats'));
    }

    /** GET /admin/pesanan/nasi-box/{id} */
    public function show(PesananNasiBox $pesanan)
    {
        $pesanan->load(['paket', 'details.komponenPaket', 'details.menu', 'buktiPembayarans', 'statusLogs.user']);
        return view('admin.pesanan.nasibox.show', compact('pesanan'));
    }

    public function konfirmasi(PesananNasiBox $pesanan)
    {
        if ($pesanan->status !== 'ditinjau') {
            return back()->with('error', 'Pesanan tidak bisa dikonfirmasi dalam status saat ini. Harus berstatus Ditinjau.');
        }

        $result = PesananNasiBoxService::potongStok($pesanan);

        if ($result === true) {
            // PRD 3.4: jika lunas → diproses, jika DP → menunggu_pelunasan
            $statusBerikutnya = ($pesanan->status_bayar === 'lunas') ? 'diproses' : 'menunggu_pelunasan';
            $pesanan->update(['status' => $statusBerikutnya]);

            if ($pesanan->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($pesanan->email)->send(new \App\Mail\PaymentReceiptMail($pesanan));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal mengirim email konfirmasi: ' . $e->getMessage());
                }
            }

            $msg = $statusBerikutnya === 'diproses'
                ? 'Pesanan dikonfirmasi (pembayaran lunas). Status langsung Sedang Diproses.'
                : 'Pesanan dikonfirmasi. Menunggu pelunasan DP dari konsumen.';

            return back()->with('success', $msg);
        }

        return back()->with('kekurangan_stok', $result)
            ->with('error', 'Stok tidak mencukupi. Silakan buat pengadaan terlebih dahulu.');
    }

    public function updateStatus(Request $request, PesananNasiBox $pesanan)
    {
        $request->validate([
            'status'       => 'required|in:ditinjau,dikonfirmasi,menunggu_pelunasan,diproses,menunggu_pengiriman,dikirim,selesai,dibatalkan',
            'alasan_batal' => 'nullable|string'
        ]);

        $newStatus = $request->status;

        // Validasi urutan status (PRD 7.5) — tidak boleh lompat
        $urutan = [
            'ditinjau'             => 0,
            'menunggu_pelunasan'   => 1,
            'terkonfirmasi'        => 1,
            'dikonfirmasi'         => 1,
            'diproses'             => 2,
            'menunggu_pengiriman'  => 3,
            'dikirim'              => 4,
            'selesai'              => 5,
            'dibatalkan'           => 99,
        ];

        $current = $urutan[$pesanan->status] ?? 0;
        $target  = $urutan[$newStatus] ?? 0;

        if ($newStatus !== 'dibatalkan' && $target > $current + 1) {
            return back()->with('error', 'Status tidak bisa dilompati. Ikuti urutan status yang benar.');
        }

        $statusLama = $pesanan->status;
        $pesanan->status = $newStatus;
        if ($newStatus === 'dibatalkan') {
            $pesanan->alasan_batal = $request->alasan_batal;
        }
        $pesanan->save();

        \App\Models\PesananStatusLog::create([
            'pesanan_type' => PesananNasiBox::class,
            'pesanan_id'   => $pesanan->id,
            'status_lama'  => $statusLama,
            'status_baru'  => $newStatus,
            'user_id'      => \Illuminate\Support\Facades\Auth::id(),
            'catatan'      => $newStatus === 'dibatalkan' ? $request->alasan_batal : null,
        ]);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function exportPdf(PesananNasiBox $pesanan)
    {
        $pesanan->load([
            'paket', 
            'details.komponenPaket',
            'details.menu.resep.bahanBaku.satuan',
            'buktiPembayarans',
            'statusLogs.user'
        ]);

        $kebutuhanBahan = [];
        foreach ($pesanan->details as $detail) {
            if ($detail->menu && $detail->menu->resep) {
                foreach ($detail->menu->resep as $resep) {
                    $bahanId = $resep->bahan_baku_id;
                    $qty = $resep->jumlah_kebutuhan * $pesanan->jumlah_box;
                    if (!isset($kebutuhanBahan[$bahanId])) {
                        $kebutuhanBahan[$bahanId] = [
                            'bahan_id' => $bahanId,
                            'nama_bahan' => $resep->bahanBaku->nama_bahan ?? 'Bahan Baku',
                            'satuan' => $resep->bahanBaku->satuan->nama_satuan ?? '',
                            'total_kebutuhan' => 0,
                        ];
                    }
                    $kebutuhanBahan[$bahanId]['total_kebutuhan'] += $qty;
                }
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pesanan.nasibox.pdf', compact('pesanan', 'kebutuhanBahan'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('Pesanan_NasiBox_' . $pesanan->kode_pesanan . '.pdf');
    }
}
