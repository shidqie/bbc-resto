<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusPembayaran extends Notification implements ShouldQueue
{
    use Queueable;

    public $pembayaran;
    public $message;
    public $url;
    public $title;

    public function __construct($pembayaran, $title, $message, $url = '#')
    {
        $this->pembayaran = $pembayaran;
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'pembayaran_id' => $this->pembayaran->id,
            'url' => $this->url,
        ];
    }
}
