<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function indexAdmin()
    {
        $notifications = Auth::guard('web')->user()->notifications()->paginate(20);
        return view('admin.notifikasi.index', compact('notifications'));
    }

    public function indexPelanggan()
    {
        $notifications = Auth::guard('pelanggan')->user()->notifications()->paginate(20);
        return view('pelanggan.notifikasi.index', compact('notifications'));
    }

    public function getUnread(Request $request)
    {
        $type = $request->query('type', 'internal');
        $user = $type === 'internal' ? Auth::guard('web')->user() : Auth::guard('pelanggan')->user();
        
        if (!$user) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        $notifications = $user->notifications()->take(10)->get()->map(function($notif) {
            return [
                'id' => $notif->id,
                'data' => $notif->data,
                'read_at' => $notif->read_at,
                'created_at_human' => $notif->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications->count()
        ]);
    }

    public function markAsRead($id)
    {
        // Try both guards
        $user = Auth::guard('web')->user() ?? Auth::guard('pelanggan')->user();
        
        if ($user) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }
        
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('pelanggan')->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
        
        return response()->json(['success' => true]);
    }
}
