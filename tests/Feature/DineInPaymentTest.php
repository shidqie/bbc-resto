<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\DetailTiketDapur;
use App\Models\JenisMenu;
use App\Models\JenisPembayaran;
use App\Models\JenisPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\Pesanan;
use App\Models\StatusMeja;
use App\Models\StatusPembayaran;
use App\Models\StatusPesanan;
use App\Models\StatusTiketDapur;
use App\Models\TiketDapur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DineInPaymentTest extends TestCase
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
        JenisMenu::create(['id' => 1, 'kode_jenis' => 'MAKANAN', 'nama_jenis' => 'Makanan']);

        StatusTiketDapur::create(['id' => 1, 'kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu']);
        StatusTiketDapur::create(['id' => 2, 'kode_status' => 'DIPROSES', 'nama_status' => 'Diproses']);
        StatusTiketDapur::create(['id' => 3, 'kode_status' => 'SELESAI', 'nama_status' => 'Selesai']);

        StatusMeja::create(['id' => 1, 'kode_status' => 'TERSEDIA', 'nama_status' => 'Tersedia']);
        StatusMeja::create(['id' => 2, 'kode_status' => 'TERISI', 'nama_status' => 'Terisi']);
        StatusMeja::create(['id' => 4, 'kode_status' => 'TIDAK_AKTIF', 'nama_status' => 'Tidak Aktif']);

        $peran = Peran::create(['id' => 1, 'nama_peran' => 'Kasir']);

        Pengguna::create([
            'id' => 1,
            'peran_id' => $peran->id,
            'nama' => 'Kasir Test',
            'email' => 'kasir@test.test',
            'kata_sandi' => bcrypt('password'),
        ]);
    }

    private function makeDineInOrder(): Pesanan
    {
        $meja = Meja::create(['id' => 1, 'kode_meja' => 'MJ-001', 'nomor_meja' => 'Meja 01', 'kapasitas' => 4, 'status_meja_id' => 1]);
        $menu = Menu::create([
            'id' => 1,
            'jenis_menu_id' => 1,
            'kode_menu' => 'MNU001',
            'nama_menu' => 'Nasi Liwet',
            'harga_jual' => 17_000,
            'status_aktif' => true,
        ]);

        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'DIN-20260802-1001',
            'tanggal_pesanan' => now(),
            'jenis_pesanan_id' => 1,
            'meja_id' => $meja->id,
            'pelayan_id' => 1,
            'status_pesanan_id' => 1,
            'total_tagihan' => 17_000,
        ]);

        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 17_000,
            'subtotal' => 17_000,
        ]);

        TiketDapur::create([
            'pesanan_id' => $pesanan->id,
            'meja_id' => $meja->id,
            'nomor_meja' => 'Meja 01',
            'nama_konsumen' => 'Tamu',
            'jumlah_tamu' => 1,
            'sumber_pesanan' => 'pos',
            'status_tiket_dapur_id' => 1,
            'dicetak_pada' => now(),
        ]);

        $meja->update(['status_meja_id' => 2]);

        return $pesanan;
    }

    public function test_pembayaran_dine_in_cash_berhasil(): void
    {
        $this->seedReferences();
        $pesanan = $this->makeDineInOrder();

        $response = $this->actingAs(Pengguna::find(1))
            ->post(route('pos.dinein.processPayment', 1), [
                'pesanan_id' => $pesanan->id,
                'metode_bayar' => 'cash',
                'total_tagihan' => 17_000,
                'jumlah_bayar' => 20_000,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('pos.dinein.success', $pesanan->id));

        $pesanan->refresh();
        $this->assertEquals(5, $pesanan->status_pesanan_id); // SELESAI
        $this->assertEquals(1, $pesanan->meja->status_meja_id); // TERSEDIA

        $pembayaran = $pesanan->pembayaran()->first();
        $this->assertNotNull($pembayaran);
        $this->assertEquals(3, $pembayaran->status_pembayaran_id); // LUNAS
        $this->assertEquals(20_000, (float) $pembayaran->jumlah_bayar);
    }

    public function test_checkout_halaman_menampilkan_total(): void
    {
        $this->seedReferences();
        $pesanan = $this->makeDineInOrder();

        $response = $this->actingAs(Pengguna::find(1))
            ->get(route('pos.dinein.checkout', $pesanan->meja_id));

        $response->assertOk();
        $response->assertSee('17.000', false);
    }
}
