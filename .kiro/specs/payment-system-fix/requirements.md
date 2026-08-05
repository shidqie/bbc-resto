# Requirements Document - Payment System Fix

## Introduction

This document defines comprehensive requirements for fixing the payment system for nasi box and catering orders. The current implementation has multiple critical issues affecting user experience and business operations, including non-functional Midtrans integration, incorrect payment calculations, broken routes, and unreliable file uploads.

## Glossary

- **Payment_System**: The complete payment processing infrastructure for online and manual payments
- **Midtrans_Service**: Third-party payment gateway providing multiple payment methods (VA, QRIS, e-Wallet, Credit Card)
- **DP_Amount**: Down payment (Uang Muka) calculated as percentage of total order value
- **Pelunasan_Amount**: Final payment amount to complete order payment
- **Snap_Widget**: Midtrans embedded payment interface component
- **Payment_Transaction**: Database record tracking Midtrans payment status and details
- **Manual_Payment**: Payment method requiring file upload of payment proof for admin verification
- **Order_Entity**: Pesanan record containing order details and payment status
- **Payment_Status**: Current state of payment processing (pending, settlement, failed, etc.)
- **Route_Handler**: Laravel controller method processing specific HTTP requests

## Requirements

### Requirement 1

**User Story:** As a customer ordering nasi box or catering, I want a fully functional online payment system with multiple payment methods, so that I can complete my order payment conveniently and securely.

#### Acceptance Criteria

1. WHEN a customer accesses the payment page, THE Payment_System SHALL load the Midtrans Snap_Widget without errors
2. WHEN the Snap_Widget loads, THE Payment_System SHALL display all available payment methods (Virtual Account, QRIS, e-Wallet, Credit Card)
3. WHEN a customer selects any payment method, THE Midtrans_Service SHALL generate valid payment credentials (VA numbers, QR codes, etc.)
4. WHEN payment credentials are generated, THE Payment_System SHALL display them clearly to the customer within 3 seconds
5. WHEN a customer completes payment via any method, THE Payment_System SHALL receive and process the webhook notification within 30 seconds
6. WHEN webhook notification is processed, THE Payment_System SHALL update the Payment_Status to settlement
7. THE Payment_System SHALL redirect customers to success page when payment is confirmed

### Requirement 2

**User Story:** As a customer, I want accurate payment calculations for both DP and pelunasan amounts, so that I pay the correct amount at each payment stage.

#### Acceptance Criteria

1. WHEN calculating DP_Amount for nasi box orders, THE Payment_System SHALL compute 25% of total order value
2. WHEN calculating DP_Amount for catering orders, THE Payment_System SHALL compute 50% of total order value  
3. WHEN calculating Pelunasan_Amount, THE Payment_System SHALL subtract all confirmed payments from total order value
4. WHEN displaying payment amount, THE Payment_System SHALL show the exact amount due for current payment stage
5. WHEN previous payments exist, THE Payment_System SHALL exclude already paid amounts from current payment calculation
6. THE Payment_System SHALL prevent payment requests when order is already fully paid
7. THE Payment_System SHALL validate payment amounts match order totals before processing

### Requirement 3

**User Story:** As a customer, I want a reliable manual payment upload system, so that I can submit payment proof when online payment is not available.

#### Acceptance Criteria

1. WHEN uploading payment proof, THE Payment_System SHALL accept JPEG, PNG, and PDF file formats
2. WHEN file is selected, THE Payment_System SHALL validate file size does not exceed 2MB
3. WHEN file upload is initiated, THE Payment_System SHALL display upload progress to customer
4. WHEN upload completes successfully, THE Payment_System SHALL store file securely in designated directory
5. WHEN upload fails, THE Payment_System SHALL display specific error message and allow retry
6. WHEN payment proof is submitted, THE Payment_System SHALL create Pembayaran record with pending status
7. WHEN payment proof is submitted, THE Payment_System SHALL send confirmation message to customer
8. THE Payment_System SHALL prevent duplicate file uploads for same payment stage

### Requirement 4

**User Story:** As a customer, I want real-time payment status updates, so that I know immediately when my payment is processed successfully.

#### Acceptance Criteria

1. WHEN payment is in progress, THE Payment_System SHALL poll payment status every 15 seconds
2. WHEN payment status changes to settlement, THE Payment_System SHALL update the page immediately
3. WHEN payment is confirmed, THE Payment_System SHALL redirect customer to success page within 5 seconds
4. WHEN polling fails, THE Payment_System SHALL provide manual status check option
5. THE Payment_System SHALL display countdown timer for payment expiration
6. WHEN payment expires, THE Payment_System SHALL show option to create new payment
7. THE Payment_System SHALL show clear status indicators for each payment stage (DP, Pelunasan)

### Requirement 5

**User Story:** As a system user, I want all payment routes to be properly configured and accessible, so that the payment flow works without 404 errors.

#### Acceptance Criteria

1. THE Payment_System SHALL provide accessible route for payment page display
2. THE Payment_System SHALL provide accessible route for Midtrans snap token generation  
3. THE Payment_System SHALL provide accessible route for manual payment file upload
4. THE Payment_System SHALL provide accessible route for payment status checking
5. THE Payment_System SHALL provide accessible route for Midtrans webhook notifications
6. THE Payment_System SHALL provide accessible route for invoice PDF generation
7. THE Payment_System SHALL handle all route requests without returning 404 errors
8. WHEN invalid route is accessed, THE Payment_System SHALL redirect to appropriate payment page

### Requirement 6

**User Story:** As a system administrator, I want comprehensive error handling and logging, so that payment issues can be diagnosed and resolved quickly.

#### Acceptance Criteria

1. WHEN Midtrans API calls fail, THE Payment_System SHALL log error details with context
2. WHEN file upload fails, THE Payment_System SHALL log failure reason and file details
3. WHEN webhook processing fails, THE Payment_System SHALL log payload and error information
4. WHEN database operations fail, THE Payment_System SHALL log query details and error messages
5. THE Payment_System SHALL provide user-friendly error messages for all failure scenarios
6. WHEN errors occur, THE Payment_System SHALL maintain system stability and allow recovery
7. THE Payment_System SHALL log all successful payment transactions for audit purposes

### Requirement 7

**User Story:** As a customer, I want secure payment processing with proper validation, so that my payment information and files are handled safely.

#### Acceptance Criteria

1. WHEN processing payments, THE Payment_System SHALL validate Midtrans webhook signatures
2. WHEN accepting file uploads, THE Payment_System SHALL scan files for malicious content
3. WHEN storing payment data, THE Payment_System SHALL encrypt sensitive information
4. WHEN displaying payment information, THE Payment_System SHALL sanitize all output
5. THE Payment_System SHALL use HTTPS for all payment-related communications
6. THE Payment_System SHALL validate user permissions before processing payment actions
7. THE Payment_System SHALL prevent SQL injection and XSS attacks in all payment forms

### Requirement 8

**User Story:** As a business owner, I want the payment system to integrate properly with order management, so that paid orders are automatically updated and processed.

#### Acceptance Criteria

1. WHEN payment is confirmed, THE Payment_System SHALL update Order_Entity status to confirmed  
2. WHEN DP payment is received, THE Payment_System SHALL change order status to allow preparation
3. WHEN full payment is received, THE Payment_System SHALL change order status to ready for delivery
4. WHEN payment status changes, THE Payment_System SHALL update related inventory and scheduling systems
5. THE Payment_System SHALL maintain payment history for each order
6. THE Payment_System SHALL prevent order processing when payments are insufficient
7. THE Payment_System SHALL sync payment status across all related system components

### Requirement 9

**User Story:** As a customer, I want fast and reliable payment page performance, so that I can complete payments without delays or timeouts.

#### Acceptance Criteria

1. WHEN accessing payment page, THE Payment_System SHALL load within 3 seconds
2. WHEN generating Midtrans tokens, THE Payment_System SHALL complete request within 5 seconds
3. WHEN uploading payment files, THE Payment_System SHALL process uploads within 10 seconds
4. WHEN checking payment status, THE Payment_System SHALL return response within 2 seconds
5. THE Payment_System SHALL handle concurrent payment requests without performance degradation
6. THE Payment_System SHALL cache frequently accessed payment data
7. THE Payment_System SHALL minimize database queries for payment page rendering

### Requirement 10

**User Story:** As a system maintainer, I want comprehensive testing coverage for payment functionality, so that payment system reliability is assured.

#### Acceptance Criteria

1. THE Payment_System SHALL include unit tests for all payment calculation methods
2. THE Payment_System SHALL include integration tests for Midtrans API interactions
3. THE Payment_System SHALL include tests for file upload validation and storage
4. THE Payment_System SHALL include tests for webhook signature verification
5. THE Payment_System SHALL include tests for payment status transitions
6. THE Payment_System SHALL include tests for error handling scenarios
7. THE Payment_System SHALL achieve minimum 90% code coverage for payment components