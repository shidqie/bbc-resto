<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JadwalPengirimanController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('date');
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

        $query = Pesanan::with(['jadwal_pesanan', 'detail_pesanan.menu', 'pengiriman'])
            ->whereIn('jenis_pesanan_id', [2, 3])
            ->whereHas('pengiriman'); // Only show orders that are sent to delivery (Jadwal Pengiriman is a worklist)

        if ($selectedDate) {
            $query->whereHas('jadwal_pesanan', function ($q) use ($selectedDate) {
                $q->whereDate('tanggal_acara', $selectedDate);
            });
        }

        if ($statusFilter !== 'Semua' && $statusFilter !== '' && $statusFilter !== null) {
            $query->whereHas('pengiriman', function ($q) use ($statusFilter) {
                if ($statusFilter == 2) {
                    $q->whereIn('status_pengiriman_id', [1, 2]);
                } else {
                    $q->where('status_pengiriman_id', $statusFilter);
                }
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id_pesanan', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }



        $orders = $query->get()->sortBy(function ($order) {
            return $order->jadwal_pesanan->tanggal_acara ? Carbon::parse($order->jadwal_pesanan->tanggal_acara)->format('H:i:s') : '23:59:59';
        })->values();

        $summaryQuery = Pesanan::whereIn('jenis_pesanan_id', [2, 3])
            ->whereHas('pengiriman');

        if ($selectedDate) {
            $summaryQuery->whereHas('jadwal_pesanan', function ($q) use ($selectedDate) {
                $q->whereDate('tanggal_acara', $selectedDate);
            });
        }
            

        
        $allSummaryOrders = $summaryQuery->get();

        $summary = [
            'Semua' => $allSummaryOrders->count(),
            'baru' => $allSummaryOrders->where('pengiriman.status_pengiriman_id', 1)->count(), // Menunggu Dikirim
            'diproses' => $allSummaryOrders->whereIn('pengiriman.status_pengiriman_id', [2, 3])->count(), // Dalam Pengiriman
            'selesai' => $allSummaryOrders->where('pengiriman.status_pengiriman_id', 4)->count(), // Sudah Diterima
            'dibatalkan' => $allSummaryOrders->where('pengiriman.status_pengiriman_id', 5)->count(),
        ];

        $kurirs = [];
        if (Auth::user()->peran_id != 6) {
            $kurirs = Pengguna::where('peran_id', 6)->where('status_aktif', 1)->get();
        }

        return view('admin.pesanan.pengiriman.index', compact(
            'selectedDate',
            'selectedMonth',
            'startOfMonth',
            'orderDates',
            'orders',
            'summary',
            'statusFilter',
            'search',
            'kurirs'
        ));
    }

    public function updateStatus(Request $request, $jenis, $id)
    {
        $request->validate([
            'status' => 'required|integer',
        ]);

        $order = Pesanan::findOrFail($id);

        // Peta status pesanan → status pengiriman (FR-17)
        $mapPengiriman = [
            3 => 1, // DIPROSES → dijadwalkan
            4 => 2, // SIAP → siap_dikirim
            5 => 4, // SELESAI → diterima
            6 => 5, // DIBATALKAN → gagal_dikirim
        ];

        DB::transaction(function () use ($order, $request, $mapPengiriman) {
            $order->status_pesanan_id = $request->status;
            $order->save();

            // Sinkron status pengiriman bila pesanan punya catatan pengiriman
            $pengiriman = Pengiriman::where('pesanan_id', $order->id)->first();
            if ($pengiriman && isset($mapPengiriman[$request->status])) {
                $update = ['status_pengiriman_id' => $mapPengiriman[$request->status]];
                if ($request->status == 5) {
                    $update['diterima_pada'] = now();
                }
                $pengiriman->update($update);
            }
        });

        return response()->json(['success' => true, 'message' => 'Status berhasil diubah', 'new_status' => $order->status_pesanan_id]);
    }

    /**
     * Transisi status pengiriman khusus (FR-17): dijadwalkan → siap → perjalanan → diterima.
     */
    public function updatePengirimanStatus(Request $request, $id)
    {
        $rules = ['status_pengiriman_id' => 'required|integer|min:1|max:5'];
        if ($request->status_pengiriman_id == 4) {
            $rules['foto_bukti'] = 'required|image|max:5120'; // max 5MB
        }
        $request->validate($rules);

        $pengiriman = Pengiriman::findOrFail($id);

        $update = ['status_pengiriman_id' => $request->status_pengiriman_id];
        if ($request->status_pengiriman_id == 3) {
            $update['berangkat_pada'] = now();
        }
        if ($request->status_pengiriman_id == 4) {
            $update['diterima_pada'] = now();
            
            if ($request->hasFile('foto_bukti')) {
                $path = $request->file('foto_bukti')->store('pengiriman', 'public');
                $update['foto_bukti_pengiriman'] = $path;
            }
        }

        try {
            DB::transaction(function () use ($pengiriman, $update, $request) {
                // Buatin ketika gagal di kirim mengulang jadwal pengiriman (reset ke 1)
                if ($request->status_pengiriman_id == 5) {
                    $update['status_pengiriman_id'] = 1;
                }

                $pengiriman->update($update);

                // Sync back to Pesanan
                if ($request->status_pengiriman_id == 4) { // Diterima -> Selesai
                    $pengiriman->pesanan->update(['status_pesanan_id' => 5]);
                    
                    // Notify Pemilik
                    $pemilik = \App\Models\Pengguna::whereHas('peran', function ($q) {
                        $q->where('nama_peran', 'Pemilik');
                    })->get();
                    if ($pemilik->count() > 0) {
                        \Illuminate\Support\Facades\Notification::send($pemilik, new \App\Notifications\StatusPesanan(
                            'Pengiriman Selesai',
                            "Pengiriman pesanan #{$pengiriman->pesanan->id_pesanan} telah selesai.",
                            route('admin.pesanan.index')
                        ));
                    }
                }

                // Notify Konsumen
                if ($pengiriman->pesanan->pelanggan) {
                    $statusTexts = [
                        2 => 'siap dikirim',
                        3 => 'dalam perjalanan',
                        4 => 'telah diterima',
                        5 => 'gagal dikirim, akan dijadwalkan ulang',
                    ];
                    $txt = $statusTexts[$request->status_pengiriman_id] ?? 'diperbarui';
                    $pengiriman->pesanan->pelanggan->notify(new \App\Notifications\StatusPesanan(
                        'Status Pengiriman',
                        "Pengiriman pesanan #{$pengiriman->pesanan->id_pesanan} Anda {$txt}.",
                        route('konsumen.pesanan.index')
                    ));
                }
            });
            $msg = 'Status pengiriman diperbarui.';
            if ($request->status_pengiriman_id == 5) {
                $msg = 'Pengiriman gagal, otomatis dijadwalkan ulang.';
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            // Log error and schedule a retry job
            \Log::error('Gagal memperbarui status pengiriman ID ' . $pengiriman->id . ': ' . $e->getMessage());
            \App\Jobs\RetryPengiriman::dispatch($pengiriman->id);
            return back()->with('error', 'Gagal memperbarui status, akan dicoba lagi otomatis.');
        }
    }

    public function assignKurir(Request $request, $id)
    {
        $request->validate([
            'kurir_id' => 'required|exists:pengguna,id',
        ]);

        $pengiriman = Pengiriman::findOrFail($id);
        $pengiriman->update([
            'ditugaskan_kepada' => $request->kurir_id,
        ]);

        $kurir = Pengguna::find($request->kurir_id);
        if ($kurir) {
            \Illuminate\Support\Facades\Notification::send([$kurir], new \App\Notifications\StatusPesanan(
                'Tugas Pengantaran Baru',
                "Anda ditugaskan untuk mengirim pesanan #{$pengiriman->pesanan->id_pesanan}.",
                route('admin.pengantaran.index')
            ));
        }

        return back()->with('success', 'Kurir berhasil ditugaskan.');
    }
}
