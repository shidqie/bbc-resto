<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PesananDineInController extends Controller
{
    public function index(Request $request)
    {
        $query = Pesanan::with(['detail_pesanan.menu', 'meja', 'status_pesanan', 'kasir', 'pelanggan', 'pembayaran'])
            ->where('jenis_pesanan_id', 1)
            ->latest('dibuat_pada');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_pesanan', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function ($p) use ($search) {
                      $p->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $status = $request->status ?? 'all';
        $statusFilter = match ($status) {
            'ditinjau' => 1,
            'terkonfirmasi' => 2,
            'diproses' => 3,
            'selesai' => 5,
            'dibatalkan' => 6,
            default => null,
        };

        if ($statusFilter !== null) {
            $query->where('status_pesanan_id', $statusFilter);
        }

        // ── Filter Status Pembayaran ────────────────────────────────
        $statusPembayaran = $request->status_pembayaran ?? 'all';
        $pembayaranFilter = match ($statusPembayaran) {
            'belum_bayar' => 3, // Menunggu Pelunasan
            'lunas' => 5,
            default => null,
        };

        if ($pembayaranFilter !== null) {
            $query->where('status_pembayaran_id', $pembayaranFilter);
        }

        // ── Filter Periode ─────────────────────────────────────────
        if ($request->has('periode') && $request->periode != '') {
            $now = \Carbon\Carbon::now();
            switch ($request->periode) {
                case 'hari_ini':
                    $query->whereDate('dibuat_pada', $now->toDateString());
                    break;
                case 'minggu_ini':
                    $query->whereBetween('dibuat_pada', [$now->startOfWeek()->toDateTimeString(), $now->endOfWeek()->toDateTimeString()]);
                    break;
                case 'bulan_ini':
                    $query->whereMonth('dibuat_pada', $now->month)->whereYear('dibuat_pada', $now->year);
                    break;
                case 'kustom':
                    if ($request->has('start_date') && $request->start_date != '') {
                        $query->whereDate('dibuat_pada', '>=', $request->start_date);
                    }
                    if ($request->has('end_date') && $request->end_date != '') {
                        $query->whereDate('dibuat_pada', '<=', $request->end_date);
                    }
                    break;
            }
        }

        $pesanans = $query->paginate(10)->withQueryString();

        $stats = [
            'baru'     => Pesanan::where('jenis_pesanan_id', 1)->where('status_pesanan_id', 1)->count(),
            'diproses' => Pesanan::where('jenis_pesanan_id', 1)->whereIn('status_pesanan_id', [2, 3, 4])->count(),
            'selesai'  => Pesanan::where('jenis_pesanan_id', 1)->where('status_pesanan_id', 5)->count(),
        ];

        return view('admin.pesanan.dine-in.index', compact('pesanans', 'stats', 'status'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_pesanan_id' => 'required|integer|in:1,2,3,4,5,6',
        ]);

        $pesanan = Pesanan::where('jenis_pesanan_id', 1)->findOrFail($id);
        $pesanan->update(['status_pesanan_id' => $request->status_pesanan_id]);

        return redirect()->back()->with('success', "Status pesanan {$pesanan->id_pesanan} berhasil diperbarui.");
    }
}
