# Enhanced Pembayaran Model

## Overview

The Pembayaran model has been enhanced to support advanced payment tracking features as outlined in Requirements 3.6, 3.7, and 8.5 of the payment-system-fix specification. These enhancements enable better file upload tracking, payment verification, and integration with payment gateways.

## New Features

### 1. Upload Progress Tracking
- **Field**: `upload_progress` (0-100)
- **Purpose**: Track file upload progress for payment proof
- **Usage**: Real-time progress updates during file uploads

### 2. File Hash and Verification
- **Field**: `file_hash` (SHA256 hash)
- **Purpose**: File integrity verification and duplicate detection
- **Usage**: Ensure uploaded files haven't been tampered with

### 3. Verification Notes
- **Field**: `verification_notes` (text)
- **Purpose**: Store admin verification comments
- **Usage**: Track manual verification process and decisions

### 4. Auto Verification Flag
- **Field**: `auto_verified` (boolean)
- **Purpose**: Distinguish between automatic and manual payment verification
- **Usage**: Payments from webhooks are auto-verified

### 5. Webhook Data Storage
- **Field**: `webhook_data` (JSON)
- **Purpose**: Store complete webhook payload from payment gateways
- **Usage**: Audit trail and debugging payment issues

### 6. Payment Method Details
- **Field**: `payment_method_details` (JSON)  
- **Purpose**: Store specific payment method information (VA numbers, QR codes, etc.)
- **Usage**: Display payment instructions to users

## Database Migration

```sql
-- New columns added to pembayaran table
ALTER TABLE pembayaran ADD COLUMN upload_progress TINYINT DEFAULT 0;
ALTER TABLE pembayaran ADD COLUMN file_hash VARCHAR(64) NULL;
ALTER TABLE pembayaran ADD COLUMN verification_notes TEXT NULL;
ALTER TABLE pembayaran ADD COLUMN auto_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE pembayaran ADD COLUMN webhook_data JSON NULL;
ALTER TABLE pembayaran ADD COLUMN payment_method_details JSON NULL;

-- Indexes for performance
CREATE INDEX idx_pembayaran_auto_verified ON pembayaran(auto_verified, status_pembayaran_id);
CREATE INDEX idx_pembayaran_file_hash ON pembayaran(file_hash);
```

## Model Enhancements

### New Methods

#### Progress Tracking
```php
// Update upload progress (0-100)
$pembayaran->updateUploadProgress(75);

// Check if upload is complete  
if ($pembayaran->isUploadComplete()) {
    // Process completed upload
}
```

#### Auto Verification
```php
// Mark payment as auto-verified with webhook data
$pembayaran->markAutoVerified([
    'transaction_id' => 'TXN123',
    'status' => 'settlement',
    'paid_at' => '2024-01-01 12:00:00'
]);

// Check if auto-verified
if ($pembayaran->isAutoVerified()) {
    // Handle auto-verified payment
}
```

#### File Management
```php
// Set file hash for integrity checking
$pembayaran->setFileHash(hash_file('sha256', $filePath));

// Add verification notes
$pembayaran->addVerificationNotes('Payment verified by admin John Doe');

// Check if has verification notes
if ($pembayaran->hasVerificationNotes()) {
    echo $pembayaran->verification_notes;
}
```

#### Payment Method Details
```php
// Store payment method details
$pembayaran->storePaymentMethodDetails([
    'va_number' => '1234567890',
    'bank' => 'BCA', 
    'expires_at' => '2024-12-31 23:59:59'
]);

// Get payment method details
$details = $pembayaran->getPaymentMethodDetails();
```

#### Webhook Data
```php
// Get webhook data
$webhookData = $pembayaran->getWebhookData();
```

### New Query Scopes

```php
// Get auto-verified payments
$autoVerified = Pembayaran::autoVerified()->get();

// Get payments requiring manual verification
$manualVerification = Pembayaran::manualVerificationRequired()->get();

// Get payments with completed uploads
$uploadCompleted = Pembayaran::uploadCompleted()->get();
```

## Usage Examples

### 1. Processing File Upload
```php
public function uploadPaymentProof(Request $request, $pembayaranId)
{
    $pembayaran = Pembayaran::findOrFail($pembayaranId);
    
    // Update progress during upload
    $pembayaran->updateUploadProgress(0);
    
    // ... file upload logic with progress callbacks ...
    
    // Set file hash when upload completes
    $pembayaran->updateUploadProgress(100);
    $pembayaran->setFileHash(hash_file('sha256', $uploadedFilePath));
    
    return response()->json(['status' => 'success']);
}
```

### 2. Processing Webhook
```php
public function processWebhook(array $webhookData)
{
    $pembayaran = Pembayaran::where('nomor_referensi', $webhookData['order_id'])->first();
    
    if ($pembayaran && $webhookData['transaction_status'] === 'settlement') {
        // Mark as auto-verified with webhook data
        $pembayaran->markAutoVerified($webhookData);
        
        // Update order status
        $pembayaran->pesanan->update(['status' => 'paid']);
    }
}
```

### 3. Admin Verification
```php
public function verifyPayment(Request $request, $pembayaranId)
{
    $pembayaran = Pembayaran::findOrFail($pembayaranId);
    
    if ($request->input('approved')) {
        $pembayaran->addVerificationNotes('Approved by ' . auth()->user()->name);
        // Update status to approved
    } else {
        $pembayaran->addVerificationNotes('Rejected: ' . $request->input('reason'));
        // Update status to rejected
    }
}
```

### 4. Generating Payment Instructions
```php
public function generatePaymentInstructions($pembayaranId)
{
    $pembayaran = Pembayaran::findOrFail($pembayaranId);
    $details = $pembayaran->getPaymentMethodDetails();
    
    if (isset($details['va_number'])) {
        return view('payment.va-instructions', compact('pembayaran', 'details'));
    }
    
    return view('payment.general-instructions', compact('pembayaran'));
}
```

## Testing

The enhanced model includes comprehensive unit tests covering:

- Field casting and validation
- Upload progress tracking
- Auto-verification workflow  
- File hash management
- Verification notes handling
- Payment method details storage
- Query scopes functionality

Run tests with:
```bash
./vendor/bin/phpunit tests/Unit/PembayaranModelTest.php
```

## Requirements Addressed

- **Requirement 3.6**: Upload progress tracking and file validation
- **Requirement 3.7**: File hash verification and admin notes 
- **Requirement 8.5**: Payment history with comprehensive tracking

## Integration Points

The enhanced Pembayaran model integrates with:

- **FileUploadService**: Progress tracking and hash validation
- **MidtransController**: Webhook data storage and auto-verification
- **PaymentController**: Manual verification and file upload handling
- **Order Management**: Status updates based on payment verification