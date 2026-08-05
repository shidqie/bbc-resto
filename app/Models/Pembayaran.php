<?php

namespace App\Models;

class Pembayaran extends BaseModel
{
    protected $table = 'pembayaran';

    protected $guarded = [];

    protected $fillable = [
        'nomor_pembayaran',
        'pesanan_id',
        'metode_pembayaran_id', 
        'status_pembayaran_id',
        'jenis_pembayaran_id',
        'diproses_oleh',
        'jumlah_bayar',
        'dibayar_pada',
        'bukti_pembayaran',
        'upload_progress',
        'file_hash',
        'verification_notes',
        'auto_verified',
        'webhook_data',
        'payment_method_details',
        'nomor_referensi',
        'catatan',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'qr_code_url',
        'expired_at',
        'response_midtrans'
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'dibayar_pada' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
        'upload_progress' => 'integer',
        'auto_verified' => 'boolean',
        'webhook_data' => 'array',
        'payment_method_details' => 'array',
        'expired_at' => 'datetime',
        'response_midtrans' => 'array'
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function metode_pembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }

    public function status_pembayaran()
    {
        return $this->belongsTo(StatusPembayaran::class, 'status_pembayaran_id');
    }

    public function jenis_pembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'jenis_pembayaran_id');
    }

    public function diproses_oleh_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'diproses_oleh');
    }

    /**
     * Check if payment proof file upload is complete
     */
    public function isUploadComplete(): bool
    {
        return $this->upload_progress >= 100;
    }

    /**
     * Check if payment is automatically verified
     */
    public function isAutoVerified(): bool
    {
        return $this->auto_verified;
    }

    /**
     * Check if payment has manual verification notes
     */
    public function hasVerificationNotes(): bool
    {
        return !empty($this->verification_notes);
    }

    /**
     * Get payment method details as array
     */
    public function getPaymentMethodDetails(): array
    {
        return $this->payment_method_details ?? [];
    }

    /**
     * Get webhook data as array  
     */
    public function getWebhookData(): array
    {
        return $this->webhook_data ?? [];
    }

    /**
     * Update upload progress
     */
    public function updateUploadProgress(int $progress): void
    {
        $this->update(['upload_progress' => max(0, min(100, $progress))]);
    }

    /**
     * Mark as auto verified with webhook data
     */
    public function markAutoVerified(array $webhookData = []): void
    {
        $this->update([
            'auto_verified' => true,
            'webhook_data' => $webhookData,
            'dibayar_pada' => now()
        ]);
    }

    /**
     * Add verification notes
     */
    public function addVerificationNotes(string $notes): void
    {
        $this->update(['verification_notes' => $notes]);
    }

    /**
     * Set file hash for verification
     */
    public function setFileHash(string $hash): void
    {
        $this->update(['file_hash' => $hash]);
    }

    /**
     * Store payment method details
     */
    public function storePaymentMethodDetails(array $details): void
    {
        $this->update(['payment_method_details' => $details]);
    }

    /**
     * Scope: Auto verified payments
     */
    public function scopeAutoVerified($query)
    {
        return $query->where('auto_verified', true);
    }

    /**
     * Scope: Manual verification required
     */
    public function scopeManualVerificationRequired($query)
    {
        return $query->where('auto_verified', false)
                    ->whereNotNull('bukti_pembayaran');
    }

    /**
     * Scope: Upload completed
     */
    public function scopeUploadCompleted($query)
    {
        return $query->where('upload_progress', '>=', 100);
    }
}
