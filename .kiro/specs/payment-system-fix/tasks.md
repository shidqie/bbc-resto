# Implementation Plan: Payment System Fix

## Overview

This implementation plan addresses critical issues in the payment system for nasi box and catering orders. The plan focuses on fixing Midtrans integration, payment calculations, manual payment uploads, missing routes, and adding comprehensive error handling and testing.

## Tasks

- [ ] 1. Create enhanced PaymentController with comprehensive methods
  - Implement unified payment page display method for all order types
  - Add proper Midtrans Snap token generation with error handling
  - Create secure manual payment file upload with validation
  - Add real-time payment status checking API endpoints
  - Add proper webhook handling with signature verification
  - _Requirements: 1.1-1.7, 5.1-5.8, 6.1-6.7_

  - [ ]* 1.1 Write unit tests for PaymentController methods
    - Test payment page display logic with different order types
    - Test Snap token generation with valid and invalid data
    - Test file upload validation and error scenarios
    - _Requirements: 10.1, 10.4, 10.6_

- [x] 2. Fix payment calculation logic and amount validation
  - [x] 2.1 Create PaymentCalculationService class
    - Implement accurate DP calculation (25% nasi box, 50% catering)
    - Add pelunasan amount calculation with existing payment deduction
    - Include payment validation and amount verification methods
    - Add methods to check payment completion status
    - _Requirements: 2.1-2.7_

  - [ ]* 2.2 Write property tests for payment calculations
    - **Property 1: DP calculation consistency**
    - **Validates: Requirements 2.1, 2.2**
    - Test that DP amounts are correctly calculated for all order types
    - **Property 2: Pelunasan calculation accuracy**  
    - **Validates: Requirements 2.3, 2.4, 2.5**
    - Test that pelunasan amounts properly subtract existing payments

- [x] 3. Implement secure file upload system for manual payments
  - [x] 3.1 Create FileUploadService class
    - Add file format validation (JPEG, PNG, PDF)
    - Implement file size validation (max 2MB)
    - Add virus scanning and malicious content detection
    - Create unique file naming and secure storage paths
    - Implement file access control and cleanup operations
    - _Requirements: 3.1-3.8, 7.2, 7.4_

  - [ ]* 3.2 Write unit tests for file upload functionality
    - Test file format and size validation
    - Test error handling for invalid files
    - Test secure file storage and naming
    - _Requirements: 10.3, 10.6_

- [ ] 4. Enhance MidtransController with proper webhook processing
  - [ ] 4.1 Fix webhook signature verification
    - Implement proper SHA512 signature verification
    - Add webhook payload validation and error handling
    - Create idempotent payment processing to prevent duplicates
    - Add comprehensive logging for webhook events
    - _Requirements: 1.5, 1.6, 6.1-6.4, 7.1_

  - [ ] 4.2 Implement MidtransIntegrationService
    - Create centralized Midtrans API communication service
    - Add Snap token generation with proper customer details
    - Implement payment status polling with fallback mechanisms
    - Add proper error handling and retry logic
    - _Requirements: 1.1-1.4, 6.1-6.6_

  - [ ]* 4.3 Write integration tests for Midtrans functionality
    - Test webhook signature verification
    - Test payment status updates
    - Test error handling scenarios
    - _Requirements: 10.2, 10.4, 10.6_

- [ ] 5. Checkpoint - Ensure all core payment functionality is working
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Enhance database models and add payment sessions
  - [x] 6.1 Update PaymentTransaction model
    - Add signature verification tracking fields
    - Include processed_at and webhook_received_at timestamps
    - Add retry_count and proper indexing
    - _Requirements: 6.4, 8.5_

  - [x] 6.2 Enhance Pembayaran model  
    - Add upload progress tracking
    - Include file hash and verification notes
    - Add auto_verified flag and webhook_data storage
    - _Requirements: 3.6, 3.7, 8.5_

  - [x] 6.3 Create payment_sessions table and model
    - Implement payment session management for token expiration
    - Add session security with unique tokens
    - Include payment type and amount tracking
    - _Requirements: 4.5, 4.6, 7.6_

- [ ] 7. Create comprehensive route configuration
  - [ ] 7.1 Add missing payment routes to web.php
    - Add unified payment page route with proper naming
    - Include manual payment upload route with CSRF protection
    - Add payment status polling route for real-time updates
    - Create invoice PDF generation route
    - _Requirements: 5.1-5.7_

  - [ ] 7.2 Fix API routes in api.php
    - Update Midtrans webhook route for proper processing
    - Add payment status checking API endpoint
    - Include Snap token generation API route
    - _Requirements: 5.2, 5.4, 5.5_

- [ ] 8. Implement real-time payment status updates and UI improvements
  - [ ] 8.1 Create JavaScript payment status polling
    - Implement 15-second polling for payment status
    - Add automatic page redirect on payment success
    - Include payment expiration countdown timer
    - Add manual status check fallback option
    - _Requirements: 4.1-4.7_

  - [ ] 8.2 Enhance payment page UI with Snap widget integration
    - Fix Snap widget loading and display issues
    - Add payment method display with proper error handling
    - Include upload progress indicators for manual payments
    - Add clear status indicators for DP and pelunasan stages
    - _Requirements: 1.1-1.7, 3.3, 4.7_

- [ ] 9. Add comprehensive error handling and logging
  - [ ] 9.1 Implement centralized error handling
    - Add specific error messages for all failure scenarios
    - Create proper exception handling with user-friendly messages
    - Implement comprehensive logging for all payment operations
    - Add error recovery mechanisms and retry logic
    - _Requirements: 6.1-6.7_

  - [ ] 9.2 Add security enhancements
    - Implement proper CSRF protection for all forms
    - Add input sanitization and XSS prevention
    - Include SQL injection prevention
    - Add proper user permission validation
    - _Requirements: 7.1-7.7_

  - [ ]* 9.3 Write tests for error handling and security
    - Test error scenarios and recovery mechanisms
    - Test security validations and input sanitization
    - Test permission checks and access control
    - _Requirements: 10.4-10.6_

- [ ] 10. Integrate payment system with order management
  - [ ] 10.1 Create order status update automation
    - Implement automatic order status updates on payment confirmation
    - Add DP payment processing to enable order preparation
    - Include full payment processing for delivery readiness
    - Create payment history tracking per order
    - _Requirements: 8.1-8.7_

  - [ ] 10.2 Add inventory and scheduling integration
    - Connect payment status to inventory management
    - Integrate with scheduling systems for confirmed orders
    - Prevent order processing when payments are insufficient
    - Sync payment status across all related components
    - _Requirements: 8.4, 8.6, 8.7_

- [ ] 11. Performance optimization and caching
  - [ ] 11.1 Implement payment data caching
    - Add caching for frequently accessed payment data
    - Optimize database queries for payment page rendering
    - Implement concurrent payment request handling
    - Add performance monitoring and optimization
    - _Requirements: 9.1-9.7_

  - [ ]* 11.2 Write performance tests
    - Test payment page load times
    - Test concurrent payment processing
    - Test database query optimization
    - _Requirements: 9.5, 10.7_

- [ ] 12. Final checkpoint and integration testing
  - [ ]* 12.1 Write comprehensive integration tests
    - Test complete payment flow from order to confirmation
    - Test both online and manual payment scenarios
    - Test webhook processing and status updates
    - Test error recovery and fallback mechanisms
    - _Requirements: 10.2, 10.5-10.7_

  - [ ] 12.2 Ensure all tests pass and system integration is complete
    - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP delivery
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation throughout development
- Property tests validate universal correctness properties for payment calculations
- Integration tests ensure proper Midtrans API communication
- Unit tests validate individual component functionality and error handling

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["2.1", "3.1", "6.1", "6.2", "6.3"] },
    { "id": 1, "tasks": ["1.1", "4.1", "4.2", "7.1", "7.2"] },
    { "id": 2, "tasks": ["2.2", "3.2", "4.3", "8.1", "9.1"] },
    { "id": 3, "tasks": ["8.2", "9.2", "10.1"] },
    { "id": 4, "tasks": ["9.3", "10.2", "11.1"] },
    { "id": 5, "tasks": ["11.2", "12.1"] }
  ]
}
```