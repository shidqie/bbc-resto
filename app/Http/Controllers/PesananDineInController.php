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
                  ->orWhere('catatan', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function ($p) use ($search) {
                      $p->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('meja', function ($m) use ($search) {
                      $m->where('nomor_meja', 'like', "%{$search}%");
                  });
            });
        }

        $status = $request->status ?? 'all';
        $statusFilter = match ($status) {
            '1', 'ditinjau', 'baru', 'menunggu', 'menunggu_konfirmasi' => 1,
            '2', 'terkonfirmasi', 'dikonfirmasi' => 2,
            '3', 'diproses', 'sedang_diproses' => 3,
            '4', 'siap', 'siap_disajikan', 'pesanan_siap' => 4,
            '8', 'dihidangkan', 'telah_dihidangkan', 'pesanan_telah_dihidangkan' => 8,
            '5', 'selesai' => 5,
            '6', 'dibatalkan' => 6,
            default => is_numeric($status) ? (int) $status : null,
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
            'baru'           => Pesanan::where('jenis_pesanan_id', 1)->where('status_pesanan_id', 1)->count(),
            'diproses'       => Pesanan::where('jenis_pesanan_id', 1)->whereIn('status_pesanan_id', [2, 3])->count(),
            'siap_disajikan' => Pesanan::where('jenis_pesanan_id', 1)->where('status_pesanan_id', 4)->count(),
            'dihidangkan'    => Pesanan::where('jenis_pesanan_id', 1)->where('status_pesanan_id', 8)->count(),
            'selesai'        => Pesanan::where('jenis_pesanan_id', 1)->where('status_pesanan_id', 5)->count(),
        ];

        return view('admin.pesanan.dine-in.index', compact('pesanans', 'stats', 'status'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_pesanan_id' => 'required|integer|in:1,2,3,4,5,6,8',
        ]);

        $roleName = auth()->user()->peran?->nama_peran ?? '';
        $isKasir = in_array($roleName, ['Kasir', 'Pelayan', 'Pemilik', 'Admin', 'Super Admin']);
        $isDapur = in_array($roleName, ['Dapur', 'Tim Dapur', 'Pemilik', 'Admin', 'Super Admin']);
        $isManajer = in_array($roleName, ['Manajer', 'Manager']);

        if ($isManajer) {
            return redirect()->back()->with('error', 'Aktor Manajer tidak memiliki hak untuk mengubah status pesanan konsumen.');
        }

        $pesanan = Pesanan::where('jenis_pesanan_id', 1)->findOrFail($id);
        $newStatus = (int) $request->status_pesanan_id;

        // Role check
        if (in_array($newStatus, [2, 5, 6, 8]) && !$isKasir) {
            return redirect()->back()->with('error', 'Hanya Kasir/Pelayan yang dapat mengonfirmasi, menghidangkan, menyelesaikan, atau membatalkan pesanan Dine-In.');
        }
        if (in_array($newStatus, [3, 4]) && !$isDapur) {
            return redirect()->back()->with('error', 'Hanya Tim Dapur yang dapat memproses dan menyiapkan pesanan.');
        }

        $pesanan->update(['status_pesanan_id' => $newStatus]);

        $statusName = match ($newStatus) {
            1 => 'Menunggu Konfirmasi',
            2 => 'Dikonfirmasi',
            3 => 'Sedang Diproses',
            4 => 'Pesanan Siap',
            8 => 'Pesanan Telah Dihidangkan',
            5 => 'Selesai',
            6 => 'Dibatalkan',
            default => 'Diperbarui',
        };

        return redirect()->back()->with('success', "Status pesanan {$pesanan->id_pesanan} berhasil diubah menjadi {$statusName}.");
    }
}
