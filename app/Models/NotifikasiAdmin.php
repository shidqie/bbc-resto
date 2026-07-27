<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiAdmin extends Model
{
    protected $fillable = [
        'judul',
        'pesan',
        'tipe',
        'link',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Helper untuk membuat notifikasi admin & kirim email ke admin
     */
    public static function buatNotifikasi($judul, $pesan, $tipe = 'pesanan_baru', $link = null)
    {
        $notif = self::create([
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'link' => $link,
            'is_read' => false,
        ]);

        // Kirim email notifikasi ke admin (jika dikonfigurasi)
        try {
            $adminEmail = config('mail.admin_address', 'admin@saungbabakancinta.com');
            if ($adminEmail) {
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\AdminNotificationMail($judul, $pesan, $link));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email notifikasi admin: ' . $e->getMessage());
        }

        return $notif;
    }
}
