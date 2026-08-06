<?php

namespace App\Http\Controllers;

use App\Models\Pengantaran;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalPengantaranController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedMonth = $request->get('month', Carbon::parse($selectedDate)->format('Y-m'));

        $startOfMonth = Carbon::parse($selectedMonth.'-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Cari semua pesanan catering dan nasibox yang ada jadwalnya dalam bulan tersebut
        $ordersInMonth = Pesanan::whereIn('jenis_pesanan_id', [2, 3])
            ->whereHas('jadwal_pesanan', function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('tanggal_acara', [$startOfMonth, $endOfMonth]);
            })
            ->whereNotIn('status_pesanan_id', [6]) // 6 = Dibatalkan
            ->with('jadwal_pesanan')
            ->get();

        $orderDates = [];
        foreach ($ordersInMonth as $order) {
            if ($order->jadwal_pesanan && $order->jadwal_pesanan->tanggal_acara) {
                $dateStr = Carbon::parse($order->jadwal_pesanan->tanggal_acara)->format('Y-m-d');
                if (! isset($orderDates[$dateStr])) {
                    $orderDates[$dateStr] = 0;
                }
                $orderDates[$dateStr]++;
            }
        }

        $statusFilter = $request->get('status', 'Semua');
        $search = $request->get('search');

        $query = Pesanan::with(['jadwal_pesanan', 'detail_pesanan.menu', 'pengantaran'])
            ->whereIn('jenis_pesanan_id', [2, 3])
            ->whereHas('pengantaran') // Only show orders that are sent to delivery (Jadwal Pengantaran is a worklist)
            ->whereHas('jadwal_pesanan', function ($q) use ($selectedDate) {
                $q->whereDate('tanggal_acara', $selectedDate);
            });

        if ($statusFilter !== 'Semua') {
            $query->whereHas('pengantaran', function ($q) use ($statusFilter) {
                $q->where('status_pengantaran_id', $statusFilter);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pesanan', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        $orders = $query->get()->sortBy(function ($order) {
            return $order->jadwal_pesanan->tanggal_acara ? Carbon::parse($order->jadwal_pesanan->tanggal_acara)->format('H:i:s') : '23:59:59';
        })->values();

        $allSummaryOrders = Pesanan::whereIn('jenis_pesanan_id', [2, 3])
            ->whereHas('pengantaran')
            ->whereHas('jadwal_pesanan', function ($q) use ($selectedDate) {
                $q->whereDate('tanggal_acara', $selectedDate);
            })->get();

        $summary = [
            'Semua' => $allSummaryOrders->count(),
            'baru' => $allSummaryOrders->where('pengantaran.status_pengantaran_id', 1)->count(), // Menunggu Dikirim
            'diproses' => $allSummaryOrders->whereIn('pengantaran.status_pengantaran_id', [2, 3])->count(), // Dalam Pengiriman
            'selesai' => $allSummaryOrders->where('pengantaran.status_pengantaran_id', 4)->count(), // Sudah Diterima
            'dibatalkan' => $allSummaryOrders->where('pengantaran.status_pengantaran_id', 5)->count(),
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
            'status' => 'required|integer',
        ]);

        $order = Pesanan::findOrFail($id);

        // Peta status pesanan → status pengantaran (FR-17)
        $mapPengantaran = [
            3 => 1, // DIPROSES → dijadwalkan
            4 => 2, // SIAP → siap_dikirim
            5 => 4, // SELESAI → diterima
            6 => 5, // DIBATALKAN → gagal_dikirim
        ];

        DB::transaction(function () use ($order, $request, $mapPengantaran) {
            $order->status_pesanan_id = $request->status;
            $order->save();

            // Sinkron status pengantaran bila pesanan punya catatan pengantaran
            $pengantaran = Pengantaran::where('pesanan_id', $order->id)->first();
            if ($pengantaran && isset($mapPengantaran[$request->status])) {
                $update = ['status_pengantaran_id' => $mapPengantaran[$request->status]];
                if ($request->status == 5) {
                    $update['diterima_pada'] = now();
                }
                $pengantaran->update($update);
            }
        });

        return response()->json(['success' => true, 'message' => 'Status berhasil diubah', 'new_status' => $order->status_pesanan_id]);
    }

    /**
     * Transisi status pengantaran khusus (FR-17): dijadwalkan → siap → perjalanan → diterima.
     */
    public function updatePengantaranStatus(Request $request, $id)
    {
        $request->validate([
            'status_pengantaran_id' => 'required|integer|min:1|max:5',
        ]);

        $pengantaran = Pengantaran::findOrFail($id);

        $update = ['status_pengantaran_id' => $request->status_pengantaran_id];
        if ($request->status_pengantaran_id == 3) {
            $update['berangkat_pada'] = now();
        }
        if ($request->status_pengantaran_id == 4) {
            $update['diterima_pada'] = now();
        }

        DB::transaction(function () use ($pengantaran, $update, $request) {
            $pengantaran->update($update);
            
            // Sync back to Pesanan
            if ($request->status_pengantaran_id == 4) { // Diterima -> Selesai
                $pengantaran->pesanan->update(['status_pesanan_id' => 5]);
            }
        });

        return back()->with('success', 'Status pengantaran diperbarui.');
    }
}
