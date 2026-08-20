<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PesananBaru extends Notification implements ShouldQueue
{
    use Queueable;

    public $pesanan;
    public $title;
    public $message;
    public $url;

    /**
     * Create a new notification instance.
     */
    public function __construct($pesanan, $title, $message, $url = '#')
    {
        $this->pesanan = $pesanan;
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'pesanan_id' => $this->pesanan->id,
            'url' => $this->url,
        ];
    }
}
