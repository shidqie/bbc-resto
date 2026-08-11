<?php

namespace App\Http\Controllers;

use App\Models\NotifikasiStok;
use App\Services\StokNotificationService;
use Illuminate\Http\Request;

class NotifikasiStokController extends Controller
{
    protected $notificationService;

    public function __construct(StokNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $jenis = $request->get('jenis');
        $dibaca = $request->get('dibaca');
        $jenisPersediaan = $request->get('jenis_persediaan');

        $query = NotifikasiStok::with('bahan_baku.satuan', 'dibacaOleh')->latest();

        if ($jenis) {
            $query->where('jenis', $jenis);
        }

        if ($jenisPersediaan) {
            $query->where('jenis_persediaan', $jenisPersediaan);
        }

        if ($dibaca !== null) {
            $query->where('dibaca', $dibaca === 'true' || $dibaca === '1');
        }

        $notifications = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total' => NotifikasiStok::count(),
            'unread' => NotifikasiStok::where('dibaca', false)->count(),
            'menipis' => NotifikasiStok::where('jenis', 'menipis')->where('dibaca', false)->count(),
            'habis' => NotifikasiStok::where('jenis', 'habis')->where('dibaca', false)->count(),
        ];

        return view('admin.persediaan.notifikasi-stok.index', compact('notifications', 'stats'));
    }

    public function markAsRead(Request $request, $id)
    {
        $this->notificationService->markAsRead($id, auth()->id());

        return response()->json(['success' => true, 'message' => 'Notifikasi ditandai dibaca']);
    }

    public function markAllAsRead(Request $request)
    {
        $count = $this->notificationService->markAllAsRead(auth()->id());

        return response()->json(['success' => true, 'message' => "{$count} notifikasi ditandai dibaca"]);
    }

    public function checkNow()
    {
        $created = $this->notificationService->checkAndNotify();

        return response()->json([
            'success' => true,
            'message' => "Pengecekan selesai. {$created} notifikasi baru dibuat.",
            'created' => $created,
        ]);
    }

    public function getUnreadCount()
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount(),
        ]);
    }
}