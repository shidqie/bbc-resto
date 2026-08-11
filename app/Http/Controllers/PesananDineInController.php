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
            default => null,
        };

        if ($statusFilter !== null) {
            $query->where('status_pesanan_id', $statusFilter);
        }

        // ── Filter Periode ─────────────────────────────────────────
        $period = $request->period ?? 'all';
        switch ($period) {
            case 'today':
                $query->whereDate('dibuat_pada', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('dibuat_pada', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('dibuat_pada', Carbon::now()->month)
                      ->whereYear('dibuat_pada', Carbon::now()->year);
                break;
        }

        $pesanans = $query->paginate(10)->withQueryString();

        $stats = [
            'baru'     => Pesanan::where('jenis_pesanan_id', 1)->where('status_pesanan_id', 1)->count(),
            'diproses' => Pesanan::where('jenis_pesanan_id', 1)->whereIn('status_pesanan_id', [2, 3, 4])->count(),
            'selesai'  => Pesanan::where('jenis_pesanan_id', 1)->where('status_pesanan_id', 5)->count(),
        ];

        return view('admin.pesanan.dine-in.index', compact('pesanans', 'stats', 'status', 'period'));
    }
}
