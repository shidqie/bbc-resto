<?php

namespace Tests\Unit;

use App\Models\Pesanan;
use App\Models\Pembayaran;
use App\Models\StatusPembayaran;
use App\Services\PaymentCalculationService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Illuminate\Support\Collection;

class PaymentCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentCalculationService();
        
        // Create test data in database
        $this->setupTestData();
    }

    protected function setupTestData(): void
    {
        // Create status pembayaran
        \DB::table('status_pembayaran')->insert([
            ['id' => 1, 'kode_status' => 'CONFIRMED', 'nama_status' => 'Confirmed'],
            ['id' => 2, 'kode_status' => 'PENDING', 'nama_status' => 'Pending'],
            ['id' => 3, 'kode_status' => 'SETTLEMENT', 'nama_status' => 'Settlement'],
        ]);

        // Create jenis pesanan 
        \DB::table('jenis_pesanan')->insert([
            ['id' => 1, 'kode_jenis' => 'DINE_IN', 'nama_jenis' => 'Dine In'],
            ['id' => 2, 'kode_jenis' => 'CATERING', 'nama_jenis' => 'Catering'],
            ['id' => 3, 'kode_jenis' => 'NASI_BOX', 'nama_jenis' => 'Nasi Box'],
        ]);

        // Create status pesanan
        \DB::table('status_pesanan')->insert([
            ['id' => 1, 'kode_status' => 'PENDING', 'nama_status' => 'Pending'],
        ]);

        // Create metode pembayaran
        \DB::table('metode_pembayaran')->insert([
            ['id' => 1, 'kode_metode' => 'TUNAI', 'nama_metode' => 'Tunai'],
        ]);

        // Create jenis pembayaran
        \DB::table('jenis_pembayaran')->insert([
            ['id' => 1, 'kode_jenis' => 'DP', 'nama_jenis' => 'Down Payment'],
        ]);
    }

    /** @test */
    public function it_calculates_dp_amount_for_nasi_box_orders()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );

        // Act
        $dpAmount = $this->service->calculateDPAmount($order);

        // Assert
        $this->assertEquals(25000, $dpAmount); // 25% of 100,000
    }

    /** @test */
    public function it_calculates_dp_amount_for_catering_orders()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );

        // Act
        $dpAmount = $this->service->calculateDPAmount($order);

        // Assert
        $this->assertEquals(100000, $dpAmount); // 50% of 200,000
    }

    /** @test */
    public function it_calculates_dp_amount_for_dine_in_orders()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_DINE_IN,
            totalTagihan: 50000
        );

        // Act
        $dpAmount = $this->service->calculateDPAmount($order);

        // Assert
        $this->assertEquals(50000, $dpAmount); // 100% for dine in
    }

    /** @test */
    public function it_calculates_pelunasan_amount_without_existing_payments()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );

        // Act
        $pelunasanAmount = $this->service->calculatePelunasanAmount($order);

        // Assert
        $this->assertEquals(200000, $pelunasanAmount); // Full amount when no payments
    }

    /** @test */
    public function it_calculates_pelunasan_amount_with_existing_dp_payment()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $pelunasanAmount = $this->service->calculatePelunasanAmount($order);

        // Assert
        $this->assertEquals(100000, $pelunasanAmount); // Remaining after DP
    }

    /** @test */
    public function it_returns_zero_pelunasan_when_fully_paid()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $pelunasanAmount = $this->service->calculatePelunasanAmount($order);

        // Assert
        $this->assertEquals(0, $pelunasanAmount);
    }

    /** @test */
    public function it_validates_correct_dp_payment_amount()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );

        // Act
        $isValid = $this->service->validatePaymentAmount(
            $order, 
            25000, 
            PaymentCalculationService::PAYMENT_TYPE_DP
        );

        // Assert
        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_validates_incorrect_dp_payment_amount()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );

        // Act
        $isValid = $this->service->validatePaymentAmount(
            $order, 
            30000, 
            PaymentCalculationService::PAYMENT_TYPE_DP
        );

        // Assert
        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_detects_completed_payment()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $isCompleted = $this->service->isPaymentCompleted($order);

        // Assert
        $this->assertTrue($isCompleted);
    }

    /** @test */
    public function it_detects_incomplete_payment()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $isCompleted = $this->service->isPaymentCompleted($order);

        // Assert
        $this->assertFalse($isCompleted);
    }

    /** @test */
    public function it_detects_dp_paid_status()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $isDPPaid = $this->service->isDPPaid($order);

        // Assert
        $this->assertTrue($isDPPaid);
    }

    /** @test */
    public function it_detects_dp_not_paid_status()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );
        $this->createConfirmedPayment($order, 50000); // Less than DP amount

        // Act
        $isDPPaid = $this->service->isDPPaid($order);

        // Assert
        $this->assertFalse($isDPPaid);
    }

    /** @test */
    public function it_provides_comprehensive_payment_summary()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $summary = $this->service->getPaymentSummary($order);

        // Assert
        $this->assertEquals(200000, $summary['total_amount']);
        $this->assertEquals(100000, $summary['dp_amount']);
        $this->assertEquals(50, $summary['dp_percentage']);
        $this->assertEquals(100000, $summary['paid_amount']);
        $this->assertEquals(100000, $summary['pelunasan_amount']);
        $this->assertTrue($summary['is_dp_paid']);
        $this->assertFalse($summary['is_completed']);
        $this->assertEquals('DP Dibayar', $summary['payment_status']);
        $this->assertEquals('Catering', $summary['order_type']);
    }

    /** @test */
    public function it_returns_next_dp_payment_when_no_payments_made()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );

        // Act
        $nextPayment = $this->service->getNextPayment($order);

        // Assert
        $this->assertNotNull($nextPayment);
        $this->assertEquals(PaymentCalculationService::PAYMENT_TYPE_DP, $nextPayment['type']);
        $this->assertEquals(25000, $nextPayment['amount']);
        $this->assertEquals('Uang Muka (DP)', $nextPayment['description']);
    }

    /** @test */
    public function it_returns_next_pelunasan_payment_when_dp_paid()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $nextPayment = $this->service->getNextPayment($order);

        // Assert
        $this->assertNotNull($nextPayment);
        $this->assertEquals(PaymentCalculationService::PAYMENT_TYPE_PELUNASAN, $nextPayment['type']);
        $this->assertEquals(100000, $nextPayment['amount']);
        $this->assertEquals('Pelunasan', $nextPayment['description']);
    }

    /** @test */
    public function it_returns_null_when_payment_completed()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $nextPayment = $this->service->getNextPayment($order);

        // Assert
        $this->assertNull($nextPayment);
    }

    /** @test */
    public function it_verifies_valid_dp_payment()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );

        // Act
        $verification = $this->service->verifyPayment(
            $order, 
            25000, 
            PaymentCalculationService::PAYMENT_TYPE_DP
        );

        // Assert
        $this->assertTrue($verification['valid']);
        $this->assertEquals('Payment verification successful', $verification['message']);
    }

    /** @test */
    public function it_rejects_payment_for_fully_paid_order()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );
        $this->createConfirmedPayment($order, 100000);

        // Act
        $verification = $this->service->verifyPayment(
            $order, 
            25000, 
            PaymentCalculationService::PAYMENT_TYPE_DP
        );

        // Assert
        $this->assertFalse($verification['valid']);
        $this->assertEquals('Order is already fully paid', $verification['error']);
    }

    /** @test */
    public function it_rejects_pelunasan_without_dp()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_CATERING,
            totalTagihan: 200000
        );

        // Act
        $verification = $this->service->verifyPayment(
            $order, 
            100000, 
            PaymentCalculationService::PAYMENT_TYPE_PELUNASAN
        );

        // Assert
        $this->assertFalse($verification['valid']);
        $this->assertEquals('DP payment is required before pelunasan', $verification['error']);
    }

    /** @test */
    public function it_rejects_zero_or_negative_amounts()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100000
        );

        // Act
        $verification = $this->service->verifyPayment(
            $order, 
            0, 
            PaymentCalculationService::PAYMENT_TYPE_DP
        );

        // Assert
        $this->assertFalse($verification['valid']);
        $this->assertEquals('Payment amount must be greater than zero', $verification['error']);
    }

    /** @test */
    public function it_throws_exception_for_invalid_order()
    {
        // Arrange
        $order = new Pesanan();
        $order->exists = false;

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order does not exist');
        
        $this->service->calculateDPAmount($order);
    }

    /** @test */
    public function it_throws_exception_for_invalid_order_total()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 0
        );

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order total amount is invalid');
        
        $this->service->calculateDPAmount($order);
    }

    /** @test */
    public function it_handles_rounding_correctly()
    {
        // Arrange
        $order = $this->createOrder(
            jenisId: PaymentCalculationService::JENIS_NASI_BOX,
            totalTagihan: 100.33
        );

        // Act
        $dpAmount = $this->service->calculateDPAmount($order);

        // Assert
        $this->assertEquals(25.08, $dpAmount); // 25% of 100.33, rounded to 2 decimal places
    }

    /**
     * Helper method to create a test order
     */
    private function createOrder(int $jenisId, float $totalTagihan): Pesanan
    {
        return Pesanan::create([
            'nomor_pesanan' => 'TEST-' . uniqid(),
            'jenis_pesanan_id' => $jenisId,
            'total_tagihan' => $totalTagihan,
            'tanggal_pesanan' => now(),
            'status_pesanan_id' => 1,
        ]);
    }

    /**
     * Helper method to create a confirmed payment
     */
    private function createConfirmedPayment(Pesanan $order, float $amount): Pembayaran
    {
        return Pembayaran::create([
            'nomor_pembayaran' => 'PAY-' . uniqid(),
            'pesanan_id' => $order->id,
            'jumlah_bayar' => $amount,
            'status_pembayaran_id' => 1, // confirmed
            'metode_pembayaran_id' => 1,
            'jenis_pembayaran_id' => 1,
            'dibayar_pada' => now(),
        ]);
    }
}