# PaymentSession Usage Examples

This document provides examples of how to use the PaymentSession model for managing payment token expiration and session security.

## Basic Usage

### Creating a Payment Session

```php
use App\Models\PaymentSession;

// Create a DP payment session (expires in 30 minutes by default)
$session = PaymentSession::createSession(
    pesananId: 1,
    paymentType: PaymentSession::TYPE_DP,
    amount: 250000.00
);

// Create a Pelunasan payment session with custom expiry
$session = PaymentSession::createSession(
    pesananId: 1,
    paymentType: PaymentSession::TYPE_PELUNASAN,
    amount: 250000.00,
    expiryMinutes: 60 // Expires in 1 hour
);
```

### Finding and Validating Sessions

```php
// Find active session by token
$session = PaymentSession::findActiveSession($token);

if ($session) {
    // Session is valid and active
    echo "Payment amount: {$session->amount}";
    echo "Time remaining: {$session->getFormattedRemainingTime()}";
} else {
    // Session expired or not found
    echo "Session expired or invalid";
}

// Check if session is still valid
if ($session->isValid()) {
    // Process payment
} else {
    // Redirect to create new session
}
```

### Managing Session Status

```php
// Mark session as completed after successful payment
$session->markCompleted();

// Extend session expiration
if ($session->extendExpiration(15)) {
    echo "Session extended by 15 minutes";
}

// Get remaining time
$seconds = $session->getRemainingSeconds();
$formatted = $session->getFormattedRemainingTime(); // "15:30"
```

### Cleanup Operations

```php
// Clean up expired sessions (for scheduled tasks)
$cleanedCount = PaymentSession::cleanupExpiredSessions();
echo "Cleaned up {$cleanedCount} expired sessions";
```

## Advanced Features

### Scopes and Queries

```php
// Get all active sessions for an order
$activeSessions = PaymentSession::where('pesanan_id', $orderId)
    ->active()
    ->get();

// Get expired sessions
$expiredSessions = PaymentSession::expired()->get();

// Find session by token
$session = PaymentSession::byToken($token)->first();
```

### Security Features

```php
// Generate secure token (done automatically on creation)
$token = PaymentSession::generateSecureToken();

// Session tokens are 64-character unique strings
// Example: "abc123...xyz789abc123...xyz789"
```

### Relationships

```php
// Access related order
$order = $session->pesanan;
echo "Order number: {$order->nomor_pesanan}";

// Access sessions from order
$order = Pesanan::find(1);
$sessions = $order->payment_sessions;
```

## Integration Examples

### Controller Usage

```php
class PaymentController extends Controller
{
    public function initializePayment(Request $request)
    {
        $pesanan = Pesanan::findOrFail($request->pesanan_id);
        
        // Create payment session
        $session = PaymentSession::createSession(
            $pesanan->id,
            $request->payment_type,
            $this->calculatePaymentAmount($pesanan, $request->payment_type)
        );
        
        return response()->json([
            'session_token' => $session->session_token,
            'expires_at' => $session->expires_at,
            'amount' => $session->amount,
            'remaining_seconds' => $session->getRemainingSeconds()
        ]);
    }
    
    public function checkPaymentSession($token)
    {
        $session = PaymentSession::findActiveSession($token);
        
        if (!$session) {
            return response()->json(['error' => 'Session expired'], 410);
        }
        
        return response()->json([
            'status' => 'active',
            'remaining_time' => $session->getFormattedRemainingTime(),
            'amount' => $session->amount
        ]);
    }
}
```

### JavaScript Frontend Integration

```javascript
// Countdown timer implementation
function startCountdown(sessionToken, initialSeconds) {
    let remaining = initialSeconds;
    
    const interval = setInterval(() => {
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        
        document.getElementById('countdown').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (remaining <= 0) {
            clearInterval(interval);
            handleSessionExpiry();
        }
        
        remaining--;
    }, 1000);
}

// Check session status periodically
function checkSessionStatus(sessionToken) {
    fetch(`/api/payment-session/${sessionToken}/status`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                handleSessionExpiry();
            } else {
                // Update UI with remaining time
                updateCountdown(data.remaining_time);
            }
        })
        .catch(() => handleSessionExpiry());
}
```

## Scheduled Cleanup

Add to your scheduled commands in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Clean up expired payment sessions every hour
    $schedule->command('payment-sessions:cleanup')
             ->hourly()
             ->withoutOverlapping();
}
```

## Validation Rules

When accepting payment session data, use these validation rules:

```php
$rules = PaymentSession::validationRules();
// Returns:
// [
//     'pesanan_id' => 'required|integer|exists:pesanan,id',
//     'payment_type' => 'required|in:dp,pelunasan',
//     'amount' => 'required|numeric|min:0.01',
// ]
```

## Requirements Fulfilled

This PaymentSession implementation addresses:

- **Requirement 4.5**: Countdown timers for payment expiration
- **Requirement 4.6**: Payment expiration handling with new payment options
- **Requirement 7.6**: Session security with unique tokens and proper validation

## Security Considerations

1. **Unique Tokens**: Each session gets a cryptographically secure 64-character token
2. **Expiration**: Sessions automatically expire and cannot be used past expiration
3. **Cleanup**: Expired sessions are marked as expired, preventing reuse
4. **Validation**: All session operations include proper validation
5. **Idempotency**: Creating new sessions automatically cancels existing active sessions