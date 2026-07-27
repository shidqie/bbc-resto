<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $judul;
    public $pesan;
    public $link;

    public function __construct($judul, $pesan, $link = null)
    {
        $this->judul = $judul;
        $this->pesan = $pesan;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('🔔 [NOTIFIKASI SISTEM] ' . $this->judul)
                    ->html("
                        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f8;'>
                            <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; border-top: 5px solid #0D3024;'>
                                <h2 style='color: #0D3024; margin-top: 0;'>🔔 " . e($this->judul) . "</h2>
                                <p style='color: #333333; font-size: 16px; line-height: 1.6;'>" . nl2br(e($this->pesan)) . "</p>
                                " . ($this->link ? "<p style='margin-top: 25px;'><a href='" . url($this->link) . "' style='background-color: #0D3024; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; inline-block;'>Buka Detail di Panel Admin</a></p>" : "") . "
                                <hr style='border: none; border-top: 1px solid #eeeeee; margin: 30px 0;'>
                                <p style='font-size: 12px; color: #888888;'>Pesan ini dikirim secara otomatis oleh Sistem Restoran Saung Babakan Cinta.</p>
                            </div>
                        </div>
                    ");
    }
}
