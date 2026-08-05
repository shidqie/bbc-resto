# Payment System Fix - Design Document

## Introduction

This document provides the technical design for fixing critical issues in the payment system for nasi box and catering orders. The solution addresses Midtrans Snap widget integration failures, incorrect payment calculations, broken manual payment uploads, missing routes, and inadequate error handling.

## Architecture Overview

### System Architecture

The payment system follows a layered architecture pattern integrating multiple components:

1. **Presentation Layer**: Laravel Blade templates with JavaScript for real-time updates and Midtrans Snap widget integration
2. **Application Layer**: Laravel controllers handling HTTP requests, business logic coordination, and response formatting
3. **Business Logic Layer**: Service classes managing payment calculations, file operations, and Midtrans API interactions
4. **Data Layer**: Eloquent ORM models with database transactions for payment records and order status management
5. **Integration Layer**: Midtrans SDK integration with webhook handling and API communication
6. **Security Layer**: CSRF protection, file validation, signature verification, and input sanitization

### Payment Flow Architecture

```
Customer Order → Payment Page → [Online Payment | Manual Upload] 
     ↓                              ↓                ↓
Order Validation            Midtrans Snap       File Upload
     ↓                              ↓                ↓
Amount Calculation         Payment Gateway      Validation
     ↓                              ↓                ↓
Token Generation           Webhook Handler      Database Storage
     ↓                              ↓                ↓
Status Polling ←── Payment Confirmation ──→ Order Update
```

### Integration Points

- **Midtrans Service**: Payment gateway API integration with token generation, webhook processing, and status polling
- **Order System**: Real-time order status updates and payment history tracking
- **Database**: Transactional payment records with audit trails and status management
- **File Storage**: Secure manual payment proof storage with validation and access control
- **Notification System**: Real-time payment status updates and confirmation messaging
## Component Design

### Controllers

#### PaymentController (Enhanced)
**Responsibilities:**
- Centralized payment processing for all order types
- Midtrans Snap token generation with proper error handling
- Payment amount calculation logic (DP vs Pelunasan)
- Manual payment proof handling with file validation
- Real-time payment status API endpoints

**Key Methods:**
```php
public function showPaymentPage($kodePesanan): View
public function generateSnapToken(Request $request): JsonResponse  
public function uploadManualPayment(Request $request): JsonResponse
public function getPaymentStatus($kodePesanan): JsonResponse
public function processWebhook(Request $request): JsonResponse
```

#### MidtransController (Fixed)
**Responsibilities:**
- Webhook notification processing with signature verification
- Payment transaction status management
- Idempotent payment processing to prevent duplicates
- Integration with order system for status updates

**Key Methods:**
```php
public function handleWebhook(Request $request): JsonResponse
public function verifySignature(array $payload): bool
public function processSuccessfulPayment(Pesanan $order, array $data): void
public function updateTransactionStatus(string $orderId, string $status): void
```

### Services

#### PaymentCalculationService
**Responsibilities:**
- Accurate payment amount calculations based on order type
- DP percentage logic (25% nasi box, 50% catering)  
- Pelunasan amount calculation with existing payment deduction
- Payment validation and amount verification

#### FileUploadService  
**Responsibilities:**
- Secure file upload handling with virus scanning
- File format and size validation (JPEG, PNG, PDF, max 2MB)
- Unique file naming and secure storage path generation
- File access control and cleanup operations

#### MidtransIntegrationService
**Responsibilities:**
- Midtrans API communication with proper error handling
- Snap token generation with customer and transaction details
- Webhook signature verification using SHA512 hash
- Payment status polling and fallback mechanisms
### Models

#### PaymentTransaction (Enhanced)
**Responsibilities:**
- Midtrans transaction tracking with comprehensive data storage
- Payment status management and audit trail
- Order-to-payment relationship mapping

**Schema Enhancements:**
```php
protected $fillable = [
    'order_id', 'din_number', 'gross_amount', 'payment_type',
    'transaction_status', 'raw_response', 'signature_verified',
    'processed_at', 'webhook_received_at', 'retry_count'
];

protected $casts = [
    'raw_response' => 'array',
    'signature_verified' => 'boolean',
    'processed_at' => 'datetime',
    'webhook_received_at' => 'datetime'
];
```

#### Pembayaran (Enhanced)
**Responsibilities:**
- Payment record management with comprehensive tracking
- Manual and automatic payment differentiation
- Payment proof storage and verification status

**New Attributes:**
```php
protected $fillable = [
    // Existing fields...
    'upload_progress', 'file_hash', 'verification_notes',
    'auto_verified', 'webhook_data', 'payment_method_details'
];
```

## Database Schema Updates

### New Tables

#### payment_sessions
```sql
CREATE TABLE payment_sessions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    pesanan_id BIGINT UNSIGNED NOT NULL,
    payment_type ENUM('dp', 'pelunasan') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    status ENUM('active', 'completed', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at)
);
```

### Enhanced Tables

#### payment_transactions (Additional Columns)
```sql
ALTER TABLE payment_transactions ADD COLUMN signature_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE payment_transactions ADD COLUMN processed_at TIMESTAMP NULL;
ALTER TABLE payment_transactions ADD COLUMN webhook_received_at TIMESTAMP NULL;
ALTER TABLE payment_transactions ADD COLUMN retry_count INT DEFAULT 0;
ALTER TABLE payment_transactions ADD INDEX idx_din_number (din_number);
ALTER TABLE payment_transactions ADD INDEX idx_transaction_status (transaction_status);
```