<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PaymentSession;
use App\Models\Pesanan;
use App\Models\JenisPesanan;
use App\Models\StatusPesanan;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PaymentSessionTest extends TestCase
{
    use RefreshDatabase;

    protected $pesanan;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required test data
        $this->createTestData();
    }

    private function createTestData()
    {
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

        // Create pelanggan
        $pelanggan = Pelanggan::create([
            'nama' => 'Test Customer',
            'nomor_telepon' => '081234567890',
            'email' => 'test@customer.com'
        ]);

        // Create test pesanan
        $this->pesanan = Pesanan::create([
            'nomor_pesanan' => 'TST-001',
            'jenis_pesanan_id' => 2, // Catering
            'pelanggan_id' => $pelanggan->id,
            'status_pesanan_id' => 1,
            'tanggal_pesanan' => now(),
            'total_tagihan' => 500000.00
        ]);
    }

    public function test_can_create_payment_session()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        $this->assertInstanceOf(PaymentSession::class, $session);
        $this->assertEquals($this->pesanan->id, $session->pesanan_id);
        $this->assertEquals(PaymentSession::TYPE_DP, $session->payment_type);
        $this->assertEquals(250000.00, $session->amount);
        $this->assertEquals(PaymentSession::STATUS_ACTIVE, $session->status);
        $this->assertNotEmpty($session->session_token);
        $this->assertTrue($session->expires_at->isFuture());
    }

    public function test_session_token_is_unique()
    {
        $session1 = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        $session2 = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_PELUNASAN,
            250000.00
        );

        $this->assertNotEquals($session1->session_token, $session2->session_token);
    }

    public function test_can_check_session_validity()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        $this->assertTrue($session->isValid());
        $this->assertFalse($session->isExpired());
    }

    public function test_can_check_expired_session()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00,
            -1 // Expired 1 minute ago
        );

        $this->assertFalse($session->isValid());
        $this->assertTrue($session->isExpired());
    }

    public function test_can_mark_session_completed()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        $result = $session->markCompleted();
        $this->assertTrue($result);
        
        $session->refresh();
        $this->assertEquals(PaymentSession::STATUS_COMPLETED, $session->status);
        $this->assertFalse($session->isValid());
    }

    public function test_can_mark_session_expired()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        $result = $session->markExpired();
        $this->assertTrue($result);
        
        $session->refresh();
        $this->assertEquals(PaymentSession::STATUS_EXPIRED, $session->status);
        $this->assertFalse($session->isValid());
    }

    public function test_can_get_remaining_seconds()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00,
            30 // 30 minutes
        );

        $remainingSeconds = $session->getRemainingSeconds();
        $this->assertGreaterThan(1700, $remainingSeconds); // Should be close to 1800 seconds (30 min)
        $this->assertLessThanOrEqual(1800, $remainingSeconds);
    }

    public function test_can_get_formatted_remaining_time()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00,
            30 // 30 minutes
        );

        $formattedTime = $session->getFormattedRemainingTime();
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $formattedTime);
        
        // Should start with 29 or 30 minutes
        $this->assertTrue(
            str_starts_with($formattedTime, '29:') || str_starts_with($formattedTime, '30:')
        );
    }

    public function test_can_find_active_session_by_token()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        $foundSession = PaymentSession::findActiveSession($session->session_token);
        $this->assertInstanceOf(PaymentSession::class, $foundSession);
        $this->assertEquals($session->id, $foundSession->id);
    }

    public function test_cannot_find_expired_session_by_token()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00,
            -1 // Expired
        );

        $foundSession = PaymentSession::findActiveSession($session->session_token);
        $this->assertNull($foundSession);
    }

    public function test_can_extend_session_expiration()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00,
            5 // 5 minutes
        );

        $originalExpiration = $session->expires_at;
        $result = $session->extendExpiration(10); // Add 10 more minutes
        
        $this->assertTrue($result);
        $session->refresh();
        $this->assertTrue($session->expires_at->isAfter($originalExpiration));
    }

    public function test_cannot_extend_expired_session()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00,
            -1 // Expired
        );

        $result = $session->extendExpiration(10);
        $this->assertFalse($result);
    }

    public function test_creates_new_session_when_active_session_exists()
    {
        // Create first session
        $session1 = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        // Create second session of same type
        $session2 = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        // First session should be cancelled
        $session1->refresh();
        $this->assertEquals(PaymentSession::STATUS_CANCELLED, $session1->status);
        
        // Second session should be active
        $this->assertEquals(PaymentSession::STATUS_ACTIVE, $session2->status);
    }

    public function test_cleanup_expired_sessions()
    {
        // Create expired session
        $expiredSession = PaymentSession::create([
            'session_token' => PaymentSession::generateSecureToken(),
            'pesanan_id' => $this->pesanan->id,
            'payment_type' => PaymentSession::TYPE_DP,
            'amount' => 250000.00,
            'expires_at' => now()->subMinutes(10),
            'status' => PaymentSession::STATUS_ACTIVE
        ]);

        // Create active session
        $activeSession = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_PELUNASAN,
            250000.00
        );

        $cleanedUp = PaymentSession::cleanupExpiredSessions();
        
        $this->assertEquals(1, $cleanedUp);
        
        $expiredSession->refresh();
        $activeSession->refresh();
        
        $this->assertEquals(PaymentSession::STATUS_EXPIRED, $expiredSession->status);
        $this->assertEquals(PaymentSession::STATUS_ACTIVE, $activeSession->status);
    }

    public function test_pesanan_relationship()
    {
        $session = PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        $this->assertInstanceOf(Pesanan::class, $session->pesanan);
        $this->assertEquals($this->pesanan->id, $session->pesanan->id);
    }

    public function test_payment_sessions_relationship_on_pesanan()
    {
        PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_DP,
            250000.00
        );

        PaymentSession::createSession(
            $this->pesanan->id,
            PaymentSession::TYPE_PELUNASAN,
            250000.00
        );

        $this->pesanan->refresh();
        $this->assertEquals(2, $this->pesanan->payment_sessions->count());
    }
}
