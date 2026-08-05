<?php

namespace Tests\Feature;

use App\Http\Controllers\MidtransController;
use App\Models\PaymentTransaction;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for MidtransController webhook signature verification and payment processing
 * 
 * Validates: Requirements 1.5, 1.6, 6.1-6.4, 7.1
 */
class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    private MidtransController $controller;
    private array $sampleWebhookData;
    private string $serverKey;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new MidtransController();
        $this->serverKey = 'Mid-server-test-key-123456789';
        config(['midtrans.server_key' => $this->serverKey]);
        
        // Create required seed data
        $this->createSeedData();
        
        // Sample webhook payload structure
        $this->sampleWebhookData = [
            'order_id' => 'TEST001-DP-1234567890',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'fraud_status' => 'accept'
        ];
    }

    /**
     * Create required seed data for tests
     */
    private function createSeedData(): void
    {
        // Create jenis_pesanan
        \DB::table('jenis_pesanan')->insert([
            'id' => 1,
            'kode_jenis' => 'CATERING',
            'nama_jenis' => 'Catering'
        ]);

        // Create status_pesanan  
        \DB::table('status_pesanan')->insert([
            'id' => 1,
            'kode_status' => 'PENDING',
            'nama_status' => 'Menunggu Pembayaran'
        ]);

        \DB::table('status_pesanan')->insert([
            'id' => 2,
            'kode_status' => 'CONFIRMED',
            'nama_status' => 'Dikonfirmasi'
        ]);
    }

    /**
     * Test proper SHA512 signature generation and verification
     * Validates: Requirement 7.1 (Security)
     */
    public function test_webhook_signature_verification_with_valid_signature()
    {
        // Generate proper signature
        $signatureString = $this->sampleWebhookData['order_id'] . 
                          $this->sampleWebhookData['status_code'] . 
                          $this->sampleWebhookData['gross_amount'] . 
                          $this->serverKey;
        
        $validSignature = hash('sha512', $signatureString);
        $this->sampleWebhookData['signature_key'] = $validSignature;

        // Create test data
        $pesanan = $this->createTestPesanan('TEST001');
        $this->createTestPaymentTransaction($this->sampleWebhookData['order_id'], 'TEST001');

        // Test webhook with valid signature
        $request = $this->createWebhookRequest($this->sampleWebhookData);
        $response = $this->controller->notificationCallback($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        // Verify signature was recorded as verified
        $transaction = PaymentTransaction::where('order_id', $this->sampleWebhookData['order_id'])->first();
        $this->assertTrue($transaction->signature_verified);
    }

    /**
     * Test webhook rejection with invalid signature
     * Validates: Requirement 7.1 (Security)
     */
    public function test_webhook_signature_verification_with_invalid_signature()
    {
        $this->sampleWebhookData['signature_key'] = 'invalid_signature_123';

        // Create test data
        $pesanan = $this->createTestPesanan('TEST001');
        $this->createTestPaymentTransaction($this->sampleWebhookData['order_id'], 'TEST001');

        // Test webhook with invalid signature
        $request = $this->createWebhookRequest($this->sampleWebhookData);
        $response = $this->controller->notificationCallback($request);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContains('Invalid signature', $response->getContent());
        
        // Verify signature was recorded as failed
        $transaction = PaymentTransaction::where('order_id', $this->sampleWebhookData['order_id'])->first();
        $this->assertFalse($transaction->signature_verified);
    }

    /**
     * Test idempotent payment processing prevents duplicates
     * Validates: Requirements 1.5, 1.6
     */
    public function test_idempotent_payment_processing_prevents_duplicates()
    {
        // Generate valid signature
        $signatureString = $this->sampleWebhookData['order_id'] . 
                          $this->sampleWebhookData['status_code'] . 
                          $this->sampleWebhookData['gross_amount'] . 
                          $this->serverKey;
        
        $this->sampleWebhookData['signature_key'] = hash('sha512', $signatureString);

        // Create test data
        $pesanan = $this->createTestPesanan('TEST001');
        $this->createTestPaymentTransaction($this->sampleWebhookData['order_id'], 'TEST001');

        // First webhook call - should create payment
        $request1 = $this->createWebhookRequest($this->sampleWebhookData);
        $response1 = $this->controller->notificationCallback($request1);
        $this->assertEquals(200, $response1->getStatusCode());

        // Verify payment was created
        $paymentCount1 = Pembayaran::where('nomor_referensi', $this->sampleWebhookData['order_id'])->count();
        $this->assertEquals(1, $paymentCount1);

        // Second webhook call with same data - should not create duplicate
        $request2 = $this->createWebhookRequest($this->sampleWebhookData);
        $response2 = $this->controller->notificationCallback($request2);
        $this->assertEquals(200, $response2->getStatusCode());

        // Verify no duplicate payment was created
        $paymentCount2 = Pembayaran::where('nomor_referensi', $this->sampleWebhookData['order_id'])->count();
        $this->assertEquals(1, $paymentCount2);
    }

    /**
     * Test webhook with missing required fields
     * Validates: Requirements 6.1-6.4 (Error Handling)
     */
    public function test_webhook_validation_with_missing_fields()
    {
        $incompleteData = [
            'order_id' => 'TEST001-DP-1234567890',
            'status_code' => '200',
            // Missing gross_amount, signature_key, transaction_status
        ];

        $request = $this->createWebhookRequest($incompleteData);
        $response = $this->controller->notificationCallback($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Missing required fields', $response->getContent());
    }

    /**
     * Test webhook with invalid JSON payload
     * Validates: Requirements 6.1-6.4 (Error Handling)
     */
    public function test_webhook_validation_with_invalid_json()
    {
        $request = Request::create('/webhook', 'POST', [], [], [], [], 'invalid_json{');
        $response = $this->controller->notificationCallback($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid JSON payload', $response->getContent());
    }

    /**
     * Test webhook logging and audit trail
     * Validates: Requirements 6.1-6.4 (Logging)
     */
    public function test_webhook_comprehensive_logging()
    {
        // Generate valid signature
        $signatureString = $this->sampleWebhookData['order_id'] . 
                          $this->sampleWebhookData['status_code'] . 
                          $this->sampleWebhookData['gross_amount'] . 
                          $this->serverKey;
        
        $this->sampleWebhookData['signature_key'] = hash('sha512', $signatureString);

        // Create test data
        $pesanan = $this->createTestPesanan('TEST001');
        $this->createTestPaymentTransaction($this->sampleWebhookData['order_id'], 'TEST001');

        // Clear existing logs and track new ones
        Log::spy();

        $request = $this->createWebhookRequest($this->sampleWebhookData);
        $response = $this->controller->notificationCallback($request);

        // Verify comprehensive logging occurred
        Log::shouldHaveReceived('info')
           ->withArgs(function ($message, $context) {
               return str_contains($message, 'Midtrans webhook received');
           });
           
        Log::shouldHaveReceived('info')
           ->withArgs(function ($message, $context) {
               return str_contains($message, 'Signature verified successfully');
           });
           
        Log::shouldHaveReceived('info')
           ->withArgs(function ($message, $context) {
               return str_contains($message, 'Webhook processing completed');
           });
    }

    /**
     * Test payment status updates in PaymentTransaction
     * Validates: Requirements 1.5, 1.6
     */
    public function test_payment_transaction_status_updates()
    {
        // Generate valid signature
        $signatureString = $this->sampleWebhookData['order_id'] . 
                          $this->sampleWebhookData['status_code'] . 
                          $this->sampleWebhookData['gross_amount'] . 
                          $this->serverKey;
        
        $this->sampleWebhookData['signature_key'] = hash('sha512', $signatureString);

        // Create test data
        $pesanan = $this->createTestPesanan('TEST001');
        $transaction = $this->createTestPaymentTransaction($this->sampleWebhookData['order_id'], 'TEST001');

        // Verify initial state
        $this->assertEquals('pending', $transaction->transaction_status);
        $this->assertFalse($transaction->signature_verified);
        $this->assertNull($transaction->processed_at);

        $request = $this->createWebhookRequest($this->sampleWebhookData);
        $response = $this->controller->notificationCallback($request);

        // Verify transaction was updated
        $transaction->refresh();
        $this->assertEquals('settlement', $transaction->transaction_status);
        $this->assertTrue($transaction->signature_verified);
        $this->assertNotNull($transaction->processed_at);
        $this->assertNotNull($transaction->webhook_received_at);
    }

    /**
     * Helper method to create test pesanan
     */
    private function createTestPesanan(string $nomorPesanan): Pesanan
    {
        return Pesanan::create([
            'nomor_pesanan' => $nomorPesanan,
            'jenis_pesanan_id' => 1,
            'status_pesanan_id' => 1,
            'tanggal_pesanan' => now(),
            'total_tagihan' => 200000,
            'jumlah_sebelum_potongan' => 200000,
        ]);
    }

    /**
     * Helper method to create test payment transaction
     */
    private function createTestPaymentTransaction(string $orderId, string $dinNumber): PaymentTransaction
    {
        return PaymentTransaction::create([
            'order_id' => $orderId,
            'din_number' => $dinNumber,
            'gross_amount' => 50000,
            'payment_type' => 'snap',
            'transaction_status' => 'pending',
            'signature_verified' => false,
            'retry_count' => 0
        ]);
    }

    /**
     * Helper method to create webhook request
     */
    private function createWebhookRequest(array $data): Request
    {
        return Request::create('/webhook', 'POST', [], [], [], [], json_encode($data));
    }
}