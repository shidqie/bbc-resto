<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PesananCatering;
use App\Models\PesananNasiBox;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JadwalPengantaranController extends Controller
{
    public function index(Request $request)
    {
        // 1. Determine selected date and month
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedMonth = $request->get('month', Carbon::parse($selectedDate)->format('Y-m'));
        
        $startOfMonth = Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // 2. Fetch dates with orders in this month for calendar dots
        $cateringDates = PesananCatering::whereBetween('tanggal_acara', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['dibatalkan'])
            ->selectRaw('DATE(tanggal_acara) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')->toArray();

        $nasiBoxDates = PesananNasiBox::whereBetween('tanggal_acara', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', ['dibatalkan'])
            ->selectRaw('DATE(tanggal_acara) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')->toArray();

        // Merge dates for calendar
        $orderDates = [];
        $allDates = array_unique(array_merge(array_keys($cateringDates), array_keys($nasiBoxDates)));
        foreach ($allDates as $date) {
            $orderDates[$date] = ($cateringDates[$date] ?? 0) + ($nasiBoxDates[$date] ?? 0);
        }

        // 3. Fetch orders for the selected date
        $statusFilter = $request->get('status', 'Semua');
        $search = $request->get('search');

        $cateringsQuery = PesananCatering::with('paket')->whereDate('tanggal_acara', $selectedDate);
        $nasiBoxQuery = PesananNasiBox::with('paket')->whereDate('tanggal_acara', $selectedDate);

        // Apply filters
        if ($statusFilter !== 'Semua') {
            $cateringsQuery->where('status', $statusFilter);
            $nasiBoxQuery->where('status', $statusFilter);
        }

        if ($search) {
            $cateringsQuery->where(function($q) use ($search) {
                $q->where('nama_pemesan', 'like', "%{$search}%")
                  ->orWhere('kode_pesanan', 'like', "%{$search}%");
            });
            $nasiBoxQuery->where(function($q) use ($search) {
                $q->where('nama_pemesan', 'like', "%{$search}%")
                  ->orWhere('kode_pesanan', 'like', "%{$search}%");
            });
        }

        $caterings = $cateringsQuery->get()->map(function($item) {
            $item->jenis = 'Catering';
            return $item;
        });

        $nasiBoxes = $nasiBoxQuery->get()->map(function($item) {
            $item->jenis = 'Nasi Box';
            return $item;
        });

        // Combine and sort by waktu_acara
        $orders = $caterings->concat($nasiBoxes)->sortBy(function($order) {
            // If waktu_acara is null, put it at the end (23:59:59)
            return $order->waktu_acara ? $order->waktu_acara : '23:59:59';
        })->values();

        // 4. Calculate summary counts for the selected date (ignoring search filter for summary)
        $summaryCaterings = PesananCatering::whereDate('tanggal_acara', $selectedDate)->get();
        $summaryNasiBoxes = PesananNasiBox::whereDate('tanggal_acara', $selectedDate)->get();
        $allSummaryOrders = $summaryCaterings->concat($summaryNasiBoxes);

        $summary = [
            'Semua' => $allSummaryOrders->count(),
            'menunggu_dp' => $allSummaryOrders->where('status', 'menunggu_dp')->count(),
            'menunggu_konfirmasi' => $allSummaryOrders->where('status', 'menunggu_konfirmasi')->count(),
            'terkonfirmasi' => $allSummaryOrders->where('status', 'terkonfirmasi')->count(),
            'diproses' => $allSummaryOrders->where('status', 'diproses')->count(),
            'dikirim' => $allSummaryOrders->where('status', 'dikirim')->count(),
            'selesai' => $allSummaryOrders->where('status', 'selesai')->count(),
            'lunas' => $allSummaryOrders->where('status', 'lunas')->count(),
            'dibatalkan' => $allSummaryOrders->where('status', 'dibatalkan')->count(),
        ];

        return view('admin.jadwal.index', compact(
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
            'status' => 'required|string'
        ]);

        if ($jenis === 'Catering') {
            $order = PesananCatering::findOrFail($id);
        } else {
            $order = PesananNasiBox::findOrFail($id);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Status berhasil diubah', 'new_status' => $order->status]);
    }
}
