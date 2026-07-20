<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pesanan;

    public function __construct($pesanan)
    {
        $this->pesanan = $pesanan;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Diterima - BBC Resto (' . $this->pesanan->kode_pesanan . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_receipt',
        );
    }
}
