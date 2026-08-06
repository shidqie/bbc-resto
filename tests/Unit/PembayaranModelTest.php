<?php

namespace Tests\Unit;

use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class PembayaranModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--force' => true]);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'nomor_pembayaran',
            'pesanan_id',
            'metode_pembayaran_id', 
            'status_pembayaran_id',
            'jenis_pembayaran_id',
            'diproses_oleh',
            'jumlah_bayar',
            'dibayar_pada',
            'bukti_pembayaran',
            'upload_progress',
            'file_hash',
            'verification_notes',
            'auto_verified',
            'webhook_data',
            'payment_method_details',
            'nomor_referensi',
            'catatan'
        ];

        $this->assertEquals($fillable, (new Pembayaran())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expected = [
            'id' => 'int',
            'jumlah_bayar' => 'decimal:2',
            'dibayar_pada' => 'datetime',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
            'upload_progress' => 'integer',
            'auto_verified' => 'boolean',
            'webhook_data' => 'array',
            'payment_method_details' => 'array'
        ];

        $model = new Pembayaran();
        $casts = $model->getCasts();
        
        foreach ($expected as $field => $type) {
            $this->assertEquals($type, $casts[$field]);
        }
    }

    /** @test */
    public function it_can_check_upload_completion_status()
    {
        $incompletePayment = new Pembayaran();
        $incompletePayment->upload_progress = 75;
        
        $completePayment = new Pembayaran();
        $completePayment->upload_progress = 100;

        $this->assertFalse($incompletePayment->isUploadComplete());
        $this->assertTrue($completePayment->isUploadComplete());
    }

    /** @test */
    public function it_can_check_auto_verification_status()
    {
        $autoVerified = new Pembayaran();
        $autoVerified->auto_verified = true;
        
        $notAutoVerified = new Pembayaran();
        $notAutoVerified->auto_verified = false;

        $this->assertTrue($autoVerified->isAutoVerified());
        $this->assertFalse($notAutoVerified->isAutoVerified());
    }

    /** @test */ 
    public function it_can_check_verification_notes()
    {
        $withNotes = new Pembayaran();
        $withNotes->verification_notes = 'Has verification notes';
        
        $withoutNotes = new Pembayaran();
        $withoutNotes->verification_notes = null;

        $this->assertTrue($withNotes->hasVerificationNotes());
        $this->assertFalse($withoutNotes->hasVerificationNotes());
    }

    /** @test */
    public function it_can_get_payment_method_details_as_array()
    {
        $pembayaran = new Pembayaran();
        $pembayaran->payment_method_details = ['va_number' => '123456'];

        $this->assertEquals(['va_number' => '123456'], $pembayaran->getPaymentMethodDetails());

        $emptyPembayaran = new Pembayaran();
        $this->assertEquals([], $emptyPembayaran->getPaymentMethodDetails());
    }

    /** @test */
    public function it_can_get_webhook_data_as_array()
    {
        $pembayaran = new Pembayaran();
        $pembayaran->webhook_data = ['transaction_id' => 'TXN123'];

        $this->assertEquals(['transaction_id' => 'TXN123'], $pembayaran->getWebhookData());

        $emptyPembayaran = new Pembayaran();
        $this->assertEquals([], $emptyPembayaran->getWebhookData());
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $pembayaran = new Pembayaran();
        $this->assertEquals('pembayaran', $pembayaran->getTable());
    }

    /** @test */
    public function database_schema_has_new_columns()
    {
        $this->assertTrue(Schema::hasColumn('pembayaran', 'upload_progress'));
        $this->assertTrue(Schema::hasColumn('pembayaran', 'file_hash'));
        $this->assertTrue(Schema::hasColumn('pembayaran', 'verification_notes'));
        $this->assertTrue(Schema::hasColumn('pembayaran', 'auto_verified'));
        $this->assertTrue(Schema::hasColumn('pembayaran', 'webhook_data'));
        $this->assertTrue(Schema::hasColumn('pembayaran', 'payment_method_details'));
    }

    /** @test */
    public function it_has_proper_relationships()
    {
        $pembayaran = new Pembayaran();
        
        // Test that relationships exist
        $this->assertTrue(method_exists($pembayaran, 'pesanan'));
        $this->assertTrue(method_exists($pembayaran, 'metode_pembayaran'));
        $this->assertTrue(method_exists($pembayaran, 'status_pembayaran'));
        $this->assertTrue(method_exists($pembayaran, 'jenis_pembayaran'));
        $this->assertTrue(method_exists($pembayaran, 'diverifikasi_oleh_pengguna'));
    }

    /** @test */
    public function it_has_progress_bounds_checking_methods()
    {
        $pembayaran = new Pembayaran();
        
        // Test the method exists
        $this->assertTrue(method_exists($pembayaran, 'updateUploadProgress'));
        $this->assertTrue(method_exists($pembayaran, 'markAutoVerified'));
        $this->assertTrue(method_exists($pembayaran, 'addVerificationNotes'));
        $this->assertTrue(method_exists($pembayaran, 'setFileHash'));
        $this->assertTrue(method_exists($pembayaran, 'storePaymentMethodDetails'));
    }

    /** @test */
    public function it_has_query_scopes()
    {
        // Test that scope methods exist
        $this->assertTrue(method_exists(Pembayaran::class, 'scopeAutoVerified'));
        $this->assertTrue(method_exists(Pembayaran::class, 'scopeManualVerificationRequired'));
        $this->assertTrue(method_exists(Pembayaran::class, 'scopeUploadCompleted'));
    }
}
