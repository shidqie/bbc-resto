<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\JenisMenu;
use App\Models\JenisMutasiStok;
use App\Models\KategoriBahanBaku;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\PurchaseOrder;
use App\Models\DetailPurchaseOrder;
use App\Models\ResepMenu;
use App\Models\Satuan;
use App\Models\StokBahan;
use App\Services\KebutuhanBahanService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutoPoOutOfStockMenuTest extends TestCase
{
    use RefreshDatabase;

    private Satuan $satuanKg;
    private Satuan $satuanGram;
    private KategoriBahanBaku $kategoriBahan;
    private KategoriMenu $kategoriMenu;
    private JenisMenu $jenisDineIn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->satuanKg = Satuan::create(['id' => 1, 'nama_satuan' => 'Kilogram', 'singkatan' => 'kg']);
        $this->satuanGram = Satuan::create(['id' => 2, 'nama_satuan' => 'Gram', 'singkatan' => 'g']);
        Satuan::create(['id' => 3, 'nama_satuan' => 'Liter', 'singkatan' => 'l']);
        Satuan::create(['id' => 4, 'nama_satuan' => 'Mililiter', 'singkatan' => 'ml']);
        Satuan::create(['id' => 10, 'nama_satuan' => 'Pcs', 'singkatan' => 'pcs']);

        $this->kategoriBahan = KategoriBahanBaku::create(['id' => 1, 'nama_kategori' => 'Daging']);
        $this->kategoriMenu = KategoriMenu::create(['id' => 1, 'nama_kategori' => 'Makanan Utama']);
        $this->jenisDineIn = JenisMenu::create(['id' => 1, 'kode_jenis' => 'DINE_IN', 'nama_jenis' => 'Dine In']);
        JenisMenu::create(['id' => 2, 'kode_jenis' => 'CATERING', 'nama_jenis' => 'Katering']);
        JenisMenu::create(['id' => 3, 'kode_jenis' => 'NASI_BOX', 'nama_jenis' => 'Nasi Box']);

        JenisMutasiStok::create(['id' => 1, 'kode_jenis' => 'MASUK', 'nama_jenis' => 'Masuk', 'arah_stok' => 'MASUK']);
        JenisMutasiStok::create(['id' => 2, 'kode_jenis' => 'KELUAR', 'nama_jenis' => 'Keluar', 'arah_stok' => 'KELUAR']);
        JenisMutasiStok::create(['id' => 3, 'kode_jenis' => 'PENYESUAIAN_MASUK', 'nama_jenis' => 'Penyesuaian Masuk', 'arah_stok' => 'MASUK']);
        JenisMutasiStok::create(['id' => 4, 'kode_jenis' => 'PENYESUAIAN_KELUAR', 'nama_jenis' => 'Penyesuaian Keluar', 'arah_stok' => 'KELUAR']);

        \App\Models\StatusPengadaan::create(['id' => 1, 'kode_status' => \App\Models\StatusPengadaan::DRAFT, 'nama_status' => 'Draft']);
        \App\Models\StatusPengadaan::create(['id' => 2, 'kode_status' => \App\Models\StatusPengadaan::MENUNGGU_PEMBELIAN, 'nama_status' => 'Menunggu Pembelian']);
        \App\Models\StatusPengadaan::create(['id' => 3, 'kode_status' => \App\Models\StatusPengadaan::DALAM_PROSES, 'nama_status' => 'Dalam Proses']);
        \App\Models\StatusPengadaan::create(['id' => 4, 'kode_status' => \App\Models\StatusPengadaan::MENUNGGU_PENERIMAAN, 'nama_status' => 'Menunggu Penerimaan']);
        \App\Models\StatusPengadaan::create(['id' => 5, 'kode_status' => \App\Models\StatusPengadaan::DITERIMA_SEBAGIAN, 'nama_status' => 'Diterima Sebagian']);
        \App\Models\StatusPengadaan::create(['id' => 6, 'kode_status' => \App\Models\StatusPengadaan::SELESAI, 'nama_status' => 'Selesai']);
        \App\Models\StatusPengadaan::create(['id' => 7, 'kode_status' => \App\Models\StatusPengadaan::DIBATALKAN, 'nama_status' => 'Dibatalkan']);
    }

    private function makeAdmin(): Pengguna
    {
        $peran = Peran::create(['id' => 1, 'nama_peran' => 'Admin']);

        return Pengguna::create([
            'id' => 1,
            'nama' => 'Admin Test',
            'email' => 'admin-autopo@bbc.com',
            'kata_sandi' => Hash::make('password'),
            'peran_id' => $peran->id,
            'status_aktif' => true,
        ]);
    }

    /**
     * Test Rumus: Kebutuhan PO = (Kebutuhan per 1 Porsi x 10) - Stok Saat Ini
     * Ayam Bakar butuh 250g ayam per porsi.
     * Stok saat ini: 100g.
     * Kebutuhan 10 porsi = 250 x 10 = 2500g.
     * Kekurangan = 2500g - 100g = 2400g = 2.4 kg.
     */
    public function test_out_of_stock_menu_formula_10_portions(): void
    {
        $bahanAyam = BahanBaku::create([
            'nama_bahan' => 'Daging Ayam',
            'satuan_id' => $this->satuanKg->id, // Purchasing unit is Kg
            'kategori_bahan_baku_id' => $this->kategoriBahan->id,
            'harga_satuan' => 40000, // Rp 40.000 / kg
            'stok_minimal' => 5000,
            'status_aktif' => true,
        ]);

        StokBahan::where('bahan_baku_id', $bahanAyam->id)
            ->where('jenis_persediaan', StokBahan::JENIS_HARIAN)
            ->update(['jumlah_stok' => 100]);

        $menu = Menu::create([
            'nama_menu' => 'Ayam Bakar',
            'harga_jual' => 30000,
            'jenis_menu_id' => $this->jenisDineIn->id,
            'kategori_menu_id' => $this->kategoriMenu->id,
            'status_aktif' => true,
        ]);

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahanAyam->id,
            'satuan_id' => $this->satuanGram->id,
            'jumlah' => 250, // 250 gram per porsi
        ]);

        $kebutuhanService = app(KebutuhanBahanService::class);
        $porsi = $kebutuhanService->porsiTersedia($menu, StokBahan::JENIS_HARIAN);
        $this->assertLessThan(1.0, $porsi);

        // Hitung Pengadaan Otomatis untuk Menu Habis
        $hasil = $kebutuhanService->hitungPengadaanMenuHabis(StokBahan::JENIS_HARIAN, 10);

        $this->assertEquals(1, $hasil['total_menu_habis']);
        $this->assertCount(1, $hasil['items']);

        $itemPo = $hasil['items']->first();
        $this->assertEquals($bahanAyam->id, $itemPo->bahan_baku_id);
        $this->assertEquals(2500, $itemPo->kebutuhan_10_porsi_base); // 250g x 10 = 2500g
        $this->assertEquals(100, $itemPo->stok_saat_ini);
        $this->assertEquals(2400, $itemPo->kekurangan_base); // 2500 - 100 = 2400g
        $this->assertEquals(2.4, $itemPo->jumlah_beli); // 2.4 kg
        $this->assertEquals('Kg', $itemPo->satuan_beli);
        $this->assertEquals(40000, $itemPo->harga_satuan);
        $this->assertContains('Ayam Bakar', $itemPo->menu_habis_terkait);
    }

    /**
     * Test bahwa jika bahan baku digunakan oleh beberapa menu Habis,
     * sistem tidak membuat data duplikat dan mengakumulasikan kebutuhan 10 porsinya.
     */
    public function test_multiple_out_of_stock_menus_sharing_same_ingredient_no_duplicates(): void
    {
        $bahanAyam = BahanBaku::create([
            'nama_bahan' => 'Daging Ayam',
            'satuan_id' => $this->satuanKg->id,
            'kategori_bahan_baku_id' => $this->kategoriBahan->id,
            'harga_satuan' => 40000,
            'stok_minimal' => 5000,
            'status_aktif' => true,
        ]);

        StokBahan::where('bahan_baku_id', $bahanAyam->id)
            ->where('jenis_persediaan', StokBahan::JENIS_HARIAN)
            ->update(['jumlah_stok' => 0]);

        // Menu 1: Ayam Bakar (250g per porsi -> 2500g untuk 10 porsi)
        $menu1 = Menu::create([
            'nama_menu' => 'Ayam Bakar',
            'harga_jual' => 30000,
            'jenis_menu_id' => $this->jenisDineIn->id,
            'kategori_menu_id' => $this->kategoriMenu->id,
            'status_aktif' => true,
        ]);
        ResepMenu::create([
            'menu_id' => $menu1->id,
            'bahan_baku_id' => $bahanAyam->id,
            'satuan_id' => $this->satuanGram->id,
            'jumlah' => 250,
        ]);

        // Menu 2: Ayam Goreng (200g per porsi -> 2000g untuk 10 porsi)
        $menu2 = Menu::create([
            'nama_menu' => 'Ayam Goreng',
            'harga_jual' => 28000,
            'jenis_menu_id' => $this->jenisDineIn->id,
            'kategori_menu_id' => $this->kategoriMenu->id,
            'status_aktif' => true,
        ]);
        ResepMenu::create([
            'menu_id' => $menu2->id,
            'bahan_baku_id' => $bahanAyam->id,
            'satuan_id' => $this->satuanGram->id,
            'jumlah' => 200,
        ]);

        $kebutuhanService = app(KebutuhanBahanService::class);
        $hasil = $kebutuhanService->hitungPengadaanMenuHabis(StokBahan::JENIS_HARIAN, 10);

        $this->assertEquals(2, $hasil['total_menu_habis']);
        // Bahan baku Ayam TIDAK boleh duplikat (tepat 1 baris)
        $this->assertCount(1, $hasil['items']);

        $itemPo = $hasil['items']->first();
        $this->assertEquals($bahanAyam->id, $itemPo->bahan_baku_id);
        // Total kebutuhan: 2500g + 2000g = 4500g = 4.5 kg
        $this->assertEquals(4500, $itemPo->kebutuhan_10_porsi_base);
        $this->assertEquals(4500, $itemPo->kekurangan_base);
        $this->assertEquals(4.5, $itemPo->jumlah_beli);
        $this->assertCount(2, $itemPo->menu_habis_terkait);
        $this->assertContains('Ayam Bakar', $itemPo->menu_habis_terkait);
        $this->assertContains('Ayam Goreng', $itemPo->menu_habis_terkait);
    }

    /**
     * Test bahwa jika stok bahan baku saat ini sudah mencukupi kebutuhan 10 porsi,
     * bahan baku tersebut TIDAK dimasukkan ke PO.
     */
    public function test_ingredient_with_sufficient_stock_for_10_portions_is_not_added(): void
    {
        $bahanAyam = BahanBaku::create([
            'nama_bahan' => 'Daging Ayam',
            'satuan_id' => $this->satuanKg->id,
            'kategori_bahan_baku_id' => $this->kategoriBahan->id,
            'harga_satuan' => 40000,
            'stok_minimal' => 5000,
            'status_aktif' => true,
        ]);
        $bahanBumbu = BahanBaku::create([
            'nama_bahan' => 'Bumbu Bakar',
            'satuan_id' => $this->satuanKg->id,
            'kategori_bahan_baku_id' => $this->kategoriBahan->id,
            'harga_satuan' => 20000,
            'stok_minimal' => 1000,
            'status_aktif' => true,
        ]);

        // Ayam stok 0 (Habis)
        StokBahan::where('bahan_baku_id', $bahanAyam->id)
            ->where('jenis_persediaan', StokBahan::JENIS_HARIAN)
            ->update(['jumlah_stok' => 0]);

        // Bumbu stok 2000g (Cukup untuk 10 porsi yang hanya butuh 500g)
        StokBahan::where('bahan_baku_id', $bahanBumbu->id)
            ->where('jenis_persediaan', StokBahan::JENIS_HARIAN)
            ->update(['jumlah_stok' => 2000]);

        $menu = Menu::create([
            'nama_menu' => 'Ayam Bakar',
            'harga_jual' => 30000,
            'jenis_menu_id' => $this->jenisDineIn->id,
            'kategori_menu_id' => $this->kategoriMenu->id,
            'status_aktif' => true,
        ]);
        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahanAyam->id,
            'satuan_id' => $this->satuanGram->id,
            'jumlah' => 250,
        ]);
        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahanBumbu->id,
            'satuan_id' => $this->satuanGram->id,
            'jumlah' => 50, // 10 porsi = 500g < stok 2000g
        ]);

        $kebutuhanService = app(KebutuhanBahanService::class);
        $hasil = $kebutuhanService->hitungPengadaanMenuHabis(StokBahan::JENIS_HARIAN, 10);

        // Hanya Ayam yang masuk PO, Bumbu tidak perlu karena stok 2000g > kebutuhan 10 porsi 500g
        $this->assertCount(1, $hasil['items']);
        $this->assertEquals($bahanAyam->id, $hasil['items']->first()->bahan_baku_id);
    }

    /**
     * Test alur lengkap:
     * Menu Habis -> Muncul di Create PO otomatis -> PO Dibuat -> Penerimaan Bahan -> Status Menu berubah menjadi Tersedia.
     */
    public function test_receiving_goods_restores_menu_status_to_tersedia(): void
    {
        $admin = $this->makeAdmin();

        $bahanAyam = BahanBaku::create([
            'nama_bahan' => 'Daging Ayam',
            'satuan_id' => $this->satuanKg->id,
            'kategori_bahan_baku_id' => $this->kategoriBahan->id,
            'harga_satuan' => 40000,
            'stok_minimal' => 5000,
            'status_aktif' => true,
        ]);

        StokBahan::where('bahan_baku_id', $bahanAyam->id)
            ->where('jenis_persediaan', StokBahan::JENIS_HARIAN)
            ->update(['jumlah_stok' => 0]);

        $menu = Menu::create([
            'nama_menu' => 'Ayam Bakar',
            'harga_jual' => 30000,
            'jenis_menu_id' => $this->jenisDineIn->id,
            'kategori_menu_id' => $this->kategoriMenu->id,
            'status_aktif' => true,
        ]);
        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahanAyam->id,
            'satuan_id' => $this->satuanGram->id,
            'jumlah' => 250,
        ]);

        $kebutuhanService = app(KebutuhanBahanService::class);
        $this->assertLessThan(1, $kebutuhanService->porsiTersedia($menu, StokBahan::JENIS_HARIAN));

        // 1. Akses halaman buat PO, verifikasi otomatis terisi bahan ayam 2.5 kg
        $response = $this->actingAs($admin)->get(route('pengadaan.po.create', ['tipe' => 'Harian']));
        $response->assertStatus(200);
        $response->assertSee('Daging Ayam');
        $response->assertSee('Menu Habis (10 Porsi)');

        // 2. Simpan PO
        $responsePo = $this->actingAs($admin)->post(route('pengadaan.po.store-unified'), [
            'tipe' => 'Operasional',
            'supplier_nama' => 'Pemasok Ayam Segar',
            'supplier_telepon' => '08123456789',
            'tanggal_po' => now()->toDateString(),
            'item_checked' => [$bahanAyam->id => 1],
            'jumlah_beli' => [$bahanAyam->id => 2.5],
            'harga_satuan' => [$bahanAyam->id => 40000],
        ]);
        $responsePo->assertRedirect(route('pengadaan.po.index'));

        $po = PurchaseOrder::with('detail_purchase_order')->latest('id')->first();
        $this->assertNotNull($po);
        $detailPo = $po->detail_purchase_order->first();

        // 3. Lakukan Penerimaan Bahan Baku sebesar 2.5 kg
        $responseTerima = $this->actingAs($admin)->post(route('pengadaan.penerimaan.store', $po->id), [
            'tanggal_penerimaan' => now()->toDateString(),
            'item_checked' => [$detailPo->id => 1],
            'jumlah_diterima' => [$detailPo->id => 2.5],
            'harga_beli' => [$detailPo->id => 40000],
            'kondisi' => [$detailPo->id => 'Baik'],
        ]);
        $responseTerima->assertRedirect();

        // 4. Verifikasi saldo stok bertambah 2500 gram
        $stokAkhir = StokBahan::where('bahan_baku_id', $bahanAyam->id)
            ->where('jenis_persediaan', StokBahan::JENIS_HARIAN)
            ->value('jumlah_stok');
        $this->assertEquals(2500, (float) $stokAkhir);

        // 5. Verifikasi menu Ayam Bakar kini statusnya TERSEDIA (porsi = 10 porsi >= 1)
        $porsiSetelahTerima = $kebutuhanService->porsiTersedia($menu, StokBahan::JENIS_HARIAN);
        $this->assertEquals(10.0, (float) $porsiSetelahTerima);
        $this->assertGreaterThanOrEqual(1, $porsiSetelahTerima);
    }
}
