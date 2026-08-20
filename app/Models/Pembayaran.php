<?php

namespace App\Models;

class Pembayaran extends BaseModel
{
    protected $table = 'pembayaran';

    protected $guarded = [];

    protected $fillable = [
        'kode_pembayaran',
        'pesanan_id',
        'metode_pembayaran', 
        'jenis_pembayaran',
        'status_verifikasi',
        'jumlah_tagihan',
        'jumlah_dibayar',
        'expires_at',
        'tanggal_pembayaran',
        'bukti_pembayaran',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
        'catatan_verifikasi',
        'upload_progress',
        'file_hash',
        'verification_notes',
        'catatan',
    ];

    protected $casts = [
        'jumlah_tagihan' => 'decimal:2',
        'jumlah_dibayar' => 'decimal:2',
        'expires_at' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
        'upload_progress' => 'integer',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function diverifikasi_oleh_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'diverifikasi_oleh');
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

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_pembayaran)) {
                $model->kode_pembayaran = \App\Helpers\IdCodeGenerator::generatePembayaranId($model->tanggal_pembayaran ?? $model->dibuat_pada ?? now());
            }
        });
    }
}
