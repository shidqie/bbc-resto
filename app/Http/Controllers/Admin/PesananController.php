<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisPesanan;
use App\Models\Pesanan;
use App\Models\StatusPesanan;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $query = Pesanan::with(['jenis_pesanan', 'status_pesanan', 'meja', 'kasir', 'jadwal_pesanan'])
            ->orderBy('dibuat_pada', 'desc');

        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis_pesanan_id', $request->jenis);
        }

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

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_pesanan', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        $pesanans = $query->paginate(20)->withQueryString();
        $jenis_pesanan = JenisPesanan::all();
        $status_pesanan = StatusPesanan::all();

        return view('admin.pesanan.index', compact('pesanans', 'jenis_pesanan', 'status_pesanan'));
    }

    public function show(Request $request, $id)
    {
        $pesanan = Pesanan::with([
            'jenis_pesanan', 'status_pesanan', 'meja', 'kasir', 'pelayan',
            'detail_pesanan.menu',
            'tiket_dapur', 'jadwal_pesanan',
        ])->findOrFail($id);

        if ($request->ajax()) {
            return view('admin.pesanan.show_partial', compact('pesanan'));
        }

        return view('admin.pesanan.show', compact('pesanan'));
    }
}
