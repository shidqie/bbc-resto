<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\DetailPesanan;
use App\Models\JenisMenu;
use App\Models\JenisMutasiStok;
use App\Models\JenisPembayaran;
use App\Models\JenisPesanan;
use App\Models\KategoriBahanBaku;
use App\Models\Menu;
use App\Models\MutasiStok;
use App\Models\Pembayaran;
use App\Models\PenerimaanBahan;
use App\Models\PengadaanBahan;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\Pesanan;
use App\Models\ResepMenu;
use App\Models\Satuan;
use App\Models\StatusPembayaran;
use App\Models\StatusPesanan;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use App\Models\DetailPengadaanBahan;
use App\Services\KebutuhanBahanService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAuditTest extends TestCase
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

        JenisPesanan::create(['id' => 1, 'kode_jenis' => 'DINE_IN', 'nama_jenis' => 'Dine In / Takeaway']);
        JenisMenu::create(['id' => 1, 'kode_jenis' => 'MAKANAN', 'nama_jenis' => 'Makanan']);

        JenisMutasiStok::create(['id' => 1, 'kode_jenis' => 'MASUK', 'nama_jenis' => 'Masuk', 'arah_stok' => 'MASUK']);
        JenisMutasiStok::create(['id' => 2, 'kode_jenis' => 'KELUAR', 'nama_jenis' => 'Keluar', 'arah_stok' => 'KELUAR']);

        $statusPengadaan = [1 => 'MENUNGGU', 2 => 'DISETUJUI', 3 => 'DITOLAK', 4 => 'SELESAI'];
        foreach ($statusPengadaan as $id => $kode) {
            StatusPengadaan::create(['id' => $id, 'kode_status' => $kode, 'nama_status' => $kode]);
        }

        $satuan = Satuan::create(['id' => 1, 'nama_satuan' => 'Gram', 'singkatan' => 'g']);
        $kategori = KategoriBahanBaku::create(['id' => 1, 'nama_kategori' => 'Bumbu']);

        $peran = Peran::create(['id' => 1, 'nama_peran' => 'Pemilik']);
        Pengguna::create([
            'id' => 1,
            'peran_id' => $peran->id,
            'nama' => 'Pemilik Test',
            'email' => 'pemilik@test.test',
            'kata_sandi' => bcrypt('password'),
        ]);

        BahanBaku::create([
            'id' => 1,
            'kategori_bahan_baku_id' => $kategori->id,
            'satuan_id' => $satuan->id,
            'kode_bahan' => 'BERAS',
            'nama_bahan' => 'Beras',
            'stok_minimal' => 10,
            'status_aktif' => true,
        ]);

        StokBahan::create([
            'bahan_baku_id' => 1,
            'jenis_persediaan' => StokBahan::JENIS_HARIAN,
            'jumlah_stok' => 500,
            'stok_minimal' => 10,
            'terakhir_diperbarui' => now(),
        ]);

        StokBahan::create([
            'bahan_baku_id' => 1,
            'jenis_persediaan' => StokBahan::JENIS_CATERING,
            'jumlah_stok' => 0,
            'stok_minimal' => 10,
            'terakhir_diperbarui' => now(),
        ]);
    }

    private function makeMenu(): Menu
    {
        return Menu::create([
            'id' => 1,
            'jenis_menu_id' => 1,
            'kode_menu' => 'MNU001',
            'nama_menu' => 'Nasi Liwet',
            'harga_jual' => 17_000,
            'status_aktif' => true,
        ]);
    }

    private function makeResep(): void
    {
        ResepMenu::create([
            'menu_id' => 1,
            'bahan_baku_id' => 1,
            'jumlah' => 100,
            'satuan_id' => 1,
        ]);
    }

    public function test_kebutuhan_bahan_menu_satuan_dihitung_dari_resep(): void
    {
        $this->seedReferences();
        $this->makeMenu();
        $this->makeResep();

        $service = app(KebutuhanBahanService::class);
        $kebutuhan = $service->kebutuhanMenu(Menu::find(1), 3);

        $this->assertCount(1, $kebutuhan);
        $this->assertEquals(1, $kebutuhan->first()['bahan_baku_id']);
        $this->assertEquals(300, $kebutuhan->first()['kebutuhan']);
    }

    public function test_bahan_cukup_dan_tidak_cukup(): void
    {
        $this->seedReferences();
        $this->makeMenu();
        $this->makeResep();

        $service = app(KebutuhanBahanService::class);
        $this->assertTrue($service->bahanCukup(Menu::find(1), 5)); // 500 >= 500
        $this->assertFalse($service->bahanCukup(Menu::find(1), 6)); // 500 < 600
    }

    public function test_complete_order_potong_stok_dan_idempoten(): void
    {
        $this->seedReferences();
        $this->makeMenu();
        $this->makeResep();

        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'DIN-0001',
            'tanggal_pesanan' => now(),
            'jenis_pesanan_id' => 1,
            'status_pesanan_id' => 1,
            'total_tagihan' => 17_000,
        ]);

        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => 1,
            'jumlah' => 2,
            'harga_satuan' => 17_000,
            'subtotal' => 34_000,
        ]);

        $orderService = app(OrderService::class);
        $orderService->completeOrder($pesanan);

        // Stok berkurang 200 (2 porsi × 100g)
        $this->assertEquals(300, (float) StokBahan::where('bahan_baku_id', 1)->where('jenis_persediaan', StokBahan::JENIS_HARIAN)->value('jumlah_stok'));

        // Mutasi tercatat dengan stok_sebelum/sesudah + referensi detail pesanan
        $mutasi = MutasiStok::first();
        $this->assertEquals(2, $mutasi->jenis_mutasi_stok_id);
        $this->assertEquals(500, (float) $mutasi->stok_sebelum);
        $this->assertEquals(300, (float) $mutasi->stok_sesudah);
        $this->assertNotNull($mutasi->detail_pesanan_id);

        // Status pesanan selesai
        $pesanan->refresh();
        $this->assertEquals(5, $pesanan->status_pesanan_id);
        $this->assertNotNull($pesanan->detail_pesanan()->first()->stock_deducted_at);

        // Idempoten: panggil ulang ditolak → stok tidak dipotong dua kali
        try {
            $orderService->completeOrder($pesanan);
            $this->fail('Pesanan yang sudah selesai seharusnya menolak completeOrder kedua.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('sudah selesai', $e->getMessage());
        }
        $this->assertEquals(300, (float) StokBahan::where('bahan_baku_id', 1)->where('jenis_persediaan', StokBahan::JENIS_HARIAN)->value('jumlah_stok'));
        $this->assertEquals(1, MutasiStok::count());
    }

    public function test_penerimaan_pengadaan_memasukkan_stok_via_stock_service(): void
    {
        $this->seedReferences();

        $pengadaan = PengadaanBahan::create([
            'nomor_pengadaan' => 'PO-0001',
            'diajukan_oleh' => 1,
            'status_pengadaan_id' => 2,
            'jenis_pengadaan' => 'harian',
            'tanggal_pengadaan' => now(),
        ]);

        DetailPengadaanBahan::create([
            'pengadaan_bahan_id' => $pengadaan->id,
            'bahan_baku_id' => 1,
            'jumlah_dipesan' => 100,
            'satuan_id' => 1,
            'harga_satuan' => 10_000,
            'subtotal' => 1_000_000,
        ]);

        $this->actingAs(Pengguna::find(1))
            ->post(route('pengadaan.proses-terima', $pengadaan->id), [
                'catatan' => 'terima PO',
                'jumlah_aktual' => [1 => 100],
                'harga_aktual' => [1 => 10_500],
            ]);

        // Stok bertambah dari 500 → 600
        $this->assertEquals(600, (float) StokBahan::where('bahan_baku_id', 1)->where('jenis_persediaan', StokBahan::JENIS_HARIAN)->value('jumlah_stok'));

        // Mutasi masuk tercatat dengan referensi detail_penerimaan_bahan
        $mutasi = MutasiStok::where('jenis_mutasi_stok_id', 1)->first();
        $this->assertNotNull($mutasi);
        $this->assertEquals(500, (float) $mutasi->stok_sebelum);
        $this->assertEquals(600, (float) $mutasi->stok_sesudah);
        $this->assertNotNull($mutasi->detail_penerimaan_bahan_id);

        $penerimaan = PenerimaanBahan::first();
        $this->assertNotNull($penerimaan);
    }
}
