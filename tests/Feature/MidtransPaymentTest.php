<?php

namespace Tests\Feature;

use App\Models\JenisPembayaran;
use App\Models\JenisPesanan;
use App\Models\MetodePembayaran;
use App\Models\PaymentTransaction;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\StatusPembayaran;
use App\Models\StatusPesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function seedReferences(): void
    {
        $statuses = [1 => 'MENUNGGU', 2 => 'DIKONFIRMASI', 3 => 'DIPROSES', 4 => 'SIAP', 5 => 'SELESAI', 6 => 'DIBATALKAN'];
        foreach ($statuses as $id => $kode) {
            StatusPesanan::create(['id' => $id, 'kode_status' => $kode, 'nama_status' => $kode]);
        }

        StatusPembayaran::create(['id' => 1, 'kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Pembayaran']);
        StatusPembayaran::create(['id' => 2, 'kode_status' => 'SEBAGIAN', 'nama_status' => 'Dibayar Sebagian']);
        StatusPembayaran::create(['id' => 3, 'kode_status' => 'LUNAS', 'nama_status' => 'Lunas']);

        JenisPembayaran::create(['id' => 1, 'kode_jenis' => 'PENUH', 'nama_jenis' => 'Pembayaran Penuh']);
        JenisPembayaran::create(['id' => 2, 'kode_jenis' => 'UANG_MUKA', 'nama_jenis' => 'Uang Muka']);
        JenisPembayaran::create(['id' => 3, 'kode_jenis' => 'PELUNASAN', 'nama_jenis' => 'Pelunasan']);

        MetodePembayaran::create(['id' => 1, 'kode_metode' => 'TUNAI', 'nama_metode' => 'Tunai']);
        MetodePembayaran::create(['id' => 2, 'kode_metode' => 'QRIS', 'nama_metode' => 'QRIS']);
        MetodePembayaran::create(['id' => 3, 'kode_metode' => 'TRANSFER', 'nama_metode' => 'Transfer Bank']);
        MetodePembayaran::create(['id' => 4, 'kode_metode' => 'KARTU', 'nama_metode' => 'Kartu Debit/Kredit']);

        JenisPesanan::create(['id' => 1, 'kode_jenis' => 'DINE_IN', 'nama_jenis' => 'Dine In / Takeaway']);
        JenisPesanan::create(['id' => 2, 'kode_jenis' => 'CATERING', 'nama_jenis' => 'Catering']);
        JenisPesanan::create(['id' => 3, 'kode_jenis' => 'NASI_BOX', 'nama_jenis' => 'Nasi Box']);
    }

    private function makePesanan(string $nomor, int $jenisId, float $total, int $statusId = 1): Pesanan
    {
        return Pesanan::create([
            'nomor_pesanan' => $nomor,
            'jenis_pesanan_id' => $jenisId,
            'status_pesanan_id' => $statusId,
            'tanggal_pesanan' => now(),
            'jumlah_sebelum_potongan' => $total,
            'total_tagihan' => $total,
        ]);
    }

    private function makeTransaction(string $orderId, string $din, int $amount, string $status = 'pending'): PaymentTransaction
    {
        return PaymentTransaction::create([
            'order_id' => $orderId,
            'din_number' => $din,
            'gross_amount' => $amount,
            'payment_type' => 'snap',
            'transaction_status' => $status,
        ]);
    }

    private function notify(array $payload): \Illuminate\Testing\TestResponse
    {
        $signature = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('midtrans.server_key'));
        $payload['signature_key'] = $signature;

        return $this->postJson('/api/midtrans/callback', $payload);
    }

    public function test_webhook_dp_payment_creates_pembayaran_dan_konfirmasi_pesanan(): void
    {
        $this->seedReferences();
        $pesanan = $this->makePesanan('CTR-20260802-099', 2, 1_000_000);
        $this->makeTransaction('CTR-20260802-099-DP-1754150000', 'CTR-20260802-099', 500_000);

        $response = $this->notify([
            'order_id' => 'CTR-20260802-099-DP-1754150000',
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran_id' => 2, // QRIS
            'status_pembayaran_id' => 3, // LUNAS
            'jenis_pembayaran_id' => 2,  // UANG_MUKA
            'jumlah_bayar' => 500_000,
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => 'CTR-20260802-099-DP-1754150000',
            'transaction_status' => 'settlement',
        ]);
        $this->assertEquals(2, $pesanan->fresh()->status_pesanan_id); // DIKONFIRMASI
    }

    public function test_webhook_pelunasan_creates_pembayaran_pelunasan(): void
    {
        $this->seedReferences();
        $pesanan = $this->makePesanan('NBX-20260802-100', 3, 2_000_000);
        $this->makeTransaction('NBX-20260802-100-LNS-1754151000', 'NBX-20260802-100', 1_500_000);

        $response = $this->notify([
            'order_id' => 'NBX-20260802-100-LNS-1754151000',
            'status_code' => '200',
            'gross_amount' => '1500000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'fraud_status' => 'accept',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran_id' => 3, // Transfer Bank
            'status_pembayaran_id' => 3, // LUNAS
            'jenis_pembayaran_id' => 3,  // PELUNASAN
            'jumlah_bayar' => 1_500_000,
        ]);
        $this->assertEquals(2, $pesanan->fresh()->status_pesanan_id); // DIKONFIRMASI
    }

    public function test_webhook_tidak_membuat_pembayaran_ganda(): void
    {
        $this->seedReferences();
        $pesanan = $this->makePesanan('CTR-20260802-101', 2, 1_000_000);
        $this->makeTransaction('CTR-20260802-101-DP-1754152000', 'CTR-20260802-101', 500_000);

        $payload = [
            'order_id' => 'CTR-20260802-101-DP-1754152000',
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ];

        $this->notify($payload)->assertOk();
        $this->notify($payload)->assertOk();

        $this->assertEquals(1, Pembayaran::where('pesanan_id', $pesanan->id)->count());
    }

    public function test_webhook_signature_invalid_ditolak(): void
    {
        $this->seedReferences();
        $this->makePesanan('CTR-20260802-102', 2, 1_000_000);

        $response = $this->postJson('/api/midtrans/callback', [
            'order_id' => 'CTR-20260802-102-DP-1754153000',
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'signature_key' => 'invalid-signature',
        ]);

        $response->assertStatus(403);
        $this->assertEquals(0, Pembayaran::count());
    }

    public function test_webhook_status_pending_tidak_membuat_pembayaran(): void
    {
        $this->seedReferences();
        $pesanan = $this->makePesanan('CTR-20260802-103', 2, 1_000_000);
        $this->makeTransaction('CTR-20260802-103-DP-1754154000', 'CTR-20260802-103', 500_000);

        $this->notify([
            'order_id' => 'CTR-20260802-103-DP-1754154000',
            'status_code' => '201',
            'gross_amount' => '500000.00',
            'transaction_status' => 'pending',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ])->assertOk();

        $this->assertEquals(0, Pembayaran::where('pesanan_id', $pesanan->id)->count());
        $this->assertEquals(1, $pesanan->fresh()->status_pesanan_id); // tetap MENUNGGU
    }

    public function test_check_status_manual_mode_lokal_menandai_lunas(): void
    {
        config(['app.env' => 'local']); // Dev Mode: simulasi pembayaran tanpa webhook

        $this->seedReferences();
        $pesanan = $this->makePesanan('CTR-20260802-104', 2, 1_000_000);
        $this->makeTransaction('CTR-20260802-104-DP-1754155000', 'CTR-20260802-104', 500_000);

        $this->get(route('pesanan.check-midtrans-status', 'CTR-20260802-104'))
            ->assertRedirect(route('pesanan.bayar', 'CTR-20260802-104'));

        $this->assertDatabaseHas('pembayaran', [
            'pesanan_id' => $pesanan->id,
            'status_pembayaran_id' => 3,
            'jumlah_bayar' => 500_000,
        ]);
    }

    public function test_persentase_dp_per_jenis_pesanan(): void
    {
        $this->seedReferences();

        $dineIn = $this->makePesanan('DIN-20260802-107', 1, 1_000_000);
        $catering = $this->makePesanan('CTR-20260802-108', 2, 1_000_000);
        $nasiBox = $this->makePesanan('NBX-20260802-109', 3, 1_000_000);

        $this->assertEquals(100, $dineIn->persentaseDP());
        $this->assertEquals(1_000_000, $dineIn->nominalDP());

        $this->assertEquals(50, $catering->persentaseDP());
        $this->assertEquals(500_000, $catering->nominalDP());

        $this->assertEquals(25, $nasiBox->persentaseDP());
        $this->assertEquals(250_000, $nasiBox->nominalDP());
    }

    public function test_halaman_bayar_menampilkan_midtrans_snap(): void
    {
        $this->seedReferences();
        $this->makePesanan('CTR-20260802-105', 2, 1_000_000);

        $response = $this->get(route('pesanan.bayar', 'CTR-20260802-105'));

        $response->assertOk();
        $response->assertSee('Ringkasan Transaksi', false);
        $response->assertSee('Batas Waktu Pembayaran', false);
        $response->assertSee('Pembayaran Online — Midtrans', false);
        $response->assertSee('snap-container', false);
        $response->assertSee("language: 'id'", false);
        $response->assertSee('Cek Status Pembayaran', false);
        $response->assertSee('snap.js');
        $response->assertSee('Bayar Manual');
        $response->assertSee('Uang muka sebesar 50%'); // Catering = 50%
    }

    public function test_halaman_bayar_lunas_menampilkan_halaman_sukses(): void
    {
        $this->seedReferences();
        $pesanan = $this->makePesanan('CTR-20260802-106', 2, 1_000_000);
        $this->makeTransaction('CTR-20260802-106-LNS-1754159000', 'CTR-20260802-106', 1_000_000, 'settlement');
        $this->notify([
            'order_id' => 'CTR-20260802-106-LNS-1754159000',
            'status_code' => '200',
            'gross_amount' => '1000000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ])->assertOk();

        $this->get(route('pesanan.bayar', 'CTR-20260802-106'))
            ->assertOk()
            ->assertSee('Pembayaran Berhasil', false);
        $this->assertEquals(1, Pembayaran::where('pesanan_id', $pesanan->id)->count());
    }

    public function test_polling_status_pembayaran_json(): void
    {
        $this->seedReferences();
        $this->makePesanan('CTR-20260802-107', 2, 1_000_000);

        $belum = $this->getJson(route('pesanan.bayar.status', 'CTR-20260802-107'))
            ->assertOk()
            ->json();
        $this->assertFalse($belum['lunas']);
        $this->assertNull($belum['transaction_status']);

        $this->makeTransaction('CTR-20260802-107-DP-1754159000', 'CTR-20260802-107', 500_000, 'pending');
        $this->notify([
            'order_id' => 'CTR-20260802-107-DP-1754159000',
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ])->assertOk();

        $dpBayar = $this->getJson(route('pesanan.bayar.status', 'CTR-20260802-107'))
            ->assertOk()
            ->json();
        $this->assertFalse($dpBayar['lunas']);
        $this->assertEquals(500_000, $dpBayar['dp_terbayar']);

        $this->makeTransaction('CTR-20260802-107-LNS-1754159001', 'CTR-20260802-107', 500_000, 'pending');
        $this->notify([
            'order_id' => 'CTR-20260802-107-LNS-1754159001',
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ])->assertOk();

        $lunas = $this->getJson(route('pesanan.bayar.status', 'CTR-20260802-107'))
            ->assertOk()
            ->json();
        $this->assertTrue($lunas['lunas']);
    }
}
