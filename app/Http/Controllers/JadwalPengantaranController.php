<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JadwalPengantaranController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedMonth = $request->get('month', Carbon::parse($selectedDate)->format('Y-m'));
        
        $startOfMonth = Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Cari semua pesanan catering dan nasibox yang ada jadwalnya dalam bulan tersebut
        $ordersInMonth = Pesanan::whereIn('jenis_pesanan_id', [2, 3])
            ->whereHas('jadwal_pesanan', function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('tanggal_acara', [$startOfMonth, $endOfMonth]);
            })
            ->whereNotIn('status_pesanan_id', [6]) // 6 = Dibatalkan
            ->with('jadwal_pesanan')
            ->get();

        $orderDates = [];
        foreach ($ordersInMonth as $order) {
            if ($order->jadwal_pesanan && $order->jadwal_pesanan->tanggal_acara) {
                $dateStr = \Carbon\Carbon::parse($order->jadwal_pesanan->tanggal_acara)->format('Y-m-d');
                if (!isset($orderDates[$dateStr])) {
                    $orderDates[$dateStr] = 0;
                }
                $orderDates[$dateStr]++;
            }
        }

        $statusFilter = $request->get('status', 'Semua');
        $search = $request->get('search');

        $query = Pesanan::with(['jadwal_pesanan', 'detail_pesanan.menu', 'pengantaran'])
            ->whereIn('jenis_pesanan_id', [2, 3])
            ->whereHas('jadwal_pesanan', function($q) use ($selectedDate) {
                $q->whereDate('tanggal_acara', $selectedDate);
            });

        if ($statusFilter !== 'Semua') {
            $query->where('status_pesanan_id', $statusFilter);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_pesanan', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        $orders = $query->get()->sortBy(function($order) {
            return $order->jadwal_pesanan->tanggal_acara ? \Carbon\Carbon::parse($order->jadwal_pesanan->tanggal_acara)->format('H:i:s') : '23:59:59';
        })->values();

        $allSummaryOrders = Pesanan::whereIn('jenis_pesanan_id', [2, 3])
            ->whereHas('jadwal_pesanan', function($q) use ($selectedDate) {
                $q->whereDate('tanggal_acara', $selectedDate);
            })->get();

        $summary = [
            'Semua' => $allSummaryOrders->count(),
            'baru' => $allSummaryOrders->where('status_pesanan_id', 1)->count(),
            'diproses' => $allSummaryOrders->whereIn('status_pesanan_id', [2,3,4])->count(),
            'selesai' => $allSummaryOrders->where('status_pesanan_id', 5)->count(),
            'dibatalkan' => $allSummaryOrders->where('status_pesanan_id', 6)->count(),
        ];

        return view('order.jadwal.index', compact(
            'selectedDate', 
            'selectedMonth', 
            'startOfMonth', 
            'orderDates', 
            'orders',
            'summary',
            'statusFilter',
            'search'
        ));
    }

    public function updateStatus(Request $request, $jenis, $id)
    {
        $request->validate([
            'status' => 'required|integer'
        ]);

        $order = Pesanan::findOrFail($id);
        $order->status_pesanan_id = $request->status;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Status berhasil diubah', 'new_status' => $order->status_pesanan_id]);
    }
}
