<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * PaymentSession Model
 * 
 * Manages payment session tokens with expiration and security features.
 * Implements session-based payment management for requirements 4.5-4.6 (countdown timers)
 * and 7.6 (security with unique tokens).
 * 
 * @property string $session_token Unique secure token for payment session
 * @property int $pesanan_id Foreign key to pesanan table
 * @property string $payment_type Type of payment: 'dp' or 'pelunasan'
 * @property float $amount Payment amount for this session
 * @property Carbon $expires_at When the payment session expires
 * @property string $status Session status: active, completed, expired, cancelled
 */
class PaymentSession extends Model
{
    protected $table = 'payment_sessions';

    protected $fillable = [
        'session_token',
        'pesanan_id',
        'payment_type',
        'amount',
        'expires_at',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'pesanan_id' => 'integer'
    ];

    // Session expiration time in minutes
    const SESSION_EXPIRY_MINUTES = 30;

    // Payment types
    const TYPE_DP = 'dp';
    const TYPE_PELUNASAN = 'pelunasan';

    // Session statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationships
     */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_EXPIRED)
              ->orWhere('expires_at', '<=', now());
        });
    }

    public function scopeByToken($query, string $token)
    {
        return $query->where('session_token', $token);
    }

    /**
     * Generate a secure unique token for payment session
     * 
     * @return string 64-character unique token
     */
    public static function generateSecureToken(): string
    {
        do {
            $token = Str::random(32) . hash('sha256', microtime(true) . mt_rand());
        } while (self::where('session_token', $token)->exists());

        return $token;
    }

    /**
     * Create a new payment session with security token
     * 
     * @param int $pesananId Order ID
     * @param string $paymentType Payment type: 'dp' or 'pelunasan'
     * @param float $amount Payment amount
     * @param int $expiryMinutes Session expiry in minutes (default: 30)
     * @return PaymentSession
     */
    public static function createSession(
        int $pesananId, 
        string $paymentType, 
        float $amount, 
        int $expiryMinutes = self::SESSION_EXPIRY_MINUTES
    ): self {
        // Cancel any existing active sessions for same order and payment type
        self::where('pesanan_id', $pesananId)
            ->where('payment_type', $paymentType)
            ->where('status', self::STATUS_ACTIVE)
            ->update(['status' => self::STATUS_CANCELLED]);

        return self::create([
            'session_token' => self::generateSecureToken(),
            'pesanan_id' => $pesananId,
            'payment_type' => $paymentType,
            'amount' => $amount,
            'expires_at' => now()->addMinutes($expiryMinutes),
            'status' => self::STATUS_ACTIVE
        ]);
    }

    /**
     * Check if session is still valid (active and not expired)
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               $this->expires_at->isFuture();
    }

    /**
     * Check if session is expired
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               $this->expires_at->isPast();
    }

    /**
     * Get remaining time until expiration in seconds
     * 
     * @return int Seconds remaining (0 if expired)
     */
    public function getRemainingSeconds(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * Get formatted remaining time (MM:SS)
     * 
     * @return string
     */
    public function getFormattedRemainingTime(): string
    {
        $seconds = $this->getRemainingSeconds();
        $minutes = intval($seconds / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Mark session as completed
     * 
     * @return bool
     */
    public function markCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED
        ]);
    }

    /**
     * Mark session as expired
     * 
     * @return bool
     */
    public function markExpired(): bool
    {
        return $this->update([
            'status' => self::STATUS_EXPIRED
        ]);
    }

    /**
     * Mark session as cancelled
     * 
     * @return bool
     */
    public function markCancelled(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED
        ]);
    }

    /**
     * Extend session expiration time
     * 
     * @param int $additionalMinutes Minutes to add
     * @return bool
     */
    public function extendExpiration(int $additionalMinutes = self::SESSION_EXPIRY_MINUTES): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return $this->update([
            'expires_at' => $this->expires_at->addMinutes($additionalMinutes)
        ]);
    }

    /**
     * Find active session by token
     * 
     * @param string $token Session token
     * @return PaymentSession|null
     */
    public static function findActiveSession(string $token): ?self
    {
        $session = self::byToken($token)->first();

        if (!$session) {
            return null;
        }

        // Auto-expire if past expiration time
        if ($session->isExpired() && $session->status === self::STATUS_ACTIVE) {
            $session->markExpired();
            return null;
        }

        return $session->isValid() ? $session : null;
    }

    /**
     * Clean up expired sessions (for scheduled cleanup)
     * 
     * @return int Number of sessions cleaned up
     */
    public static function cleanupExpiredSessions(): int
    {
        return self::where('expires_at', '<=', now())
                  ->where('status', self::STATUS_ACTIVE)
                  ->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Validation rules for payment session
     * 
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'pesanan_id' => 'required|integer|exists:pesanan,id',
            'payment_type' => 'required|in:' . self::TYPE_DP . ',' . self::TYPE_PELUNASAN,
            'amount' => 'required|numeric|min:0.01',
        ];
    }

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate token on creation if not provided
        static::creating(function ($model) {
            if (empty($model->session_token)) {
                $model->session_token = self::generateSecureToken();
            }
        });
    }
}