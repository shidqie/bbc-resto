<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\DetailPengadaanBahan;
use App\Models\JenisMenu;
use App\Models\JenisMutasiStok;
use App\Models\KategoriBahanBaku;
use App\Models\Menu;
use App\Models\MutasiStok;
use App\Models\NotifikasiStok;
use App\Models\PengadaanBahan;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\ResepMenu;
use App\Models\Satuan;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use App\Services\KebutuhanBahanService;
use App\Services\StockService;
use App\Services\StokNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pemisahan persediaan Harian vs Catering (MD: Resep & Persediaan Bahan Baku).
 *
 * Stok Bahan Baku Harian (Dine-In + Nasi Box) dan Stok Bahan Baku Catering
 * merupakan saldo yang terpisah. Pengadaan, penerimaan, penyesuaian, dan
 * notifikasi stok menipis mengikuti jenis persediaan masing-masing.
 */
class HarianCateringSplitTest extends TestCase
{
    use RefreshDatabase;

    private function makeManajer(): Pengguna
    {
        $peran = Peran::create(['id' => 2, 'nama_peran' => 'Manajer']);

        return Pengguna::create([
            'id' => 2,
            'nama' => 'Manajer Test',
            'email' => 'manajer-split@bbc.com',
            'kata_sandi' => Hash::make('password'),
            'peran_id' => $peran->id,
            'status_aktif' => true,
        ]);
    }

    private function seedReferences(): void
    {
        $satuan = Satuan::create(['id' => 1, 'nama_satuan' => 'Gram', 'singkatan' => 'g']);
        $kategori = KategoriBahanBaku::create(['id' => 1, 'nama_kategori' => 'Bumbu']);

        JenisMutasiStok::create(['id' => 1, 'kode_jenis' => 'MASUK', 'nama_jenis' => 'Masuk', 'arah_stok' => 'MASUK']);
        JenisMutasiStok::create(['id' => 2, 'kode_jenis' => 'KELUAR', 'nama_jenis' => 'Keluar', 'arah_stok' => 'KELUAR']);
        JenisMutasiStok::create(['id' => 3, 'kode_jenis' => 'PENYESUAIAN_MASUK', 'nama_jenis' => 'Penyesuaian Masuk', 'arah_stok' => 'MASUK']);
        JenisMutasiStok::create(['id' => 4, 'kode_jenis' => 'PENYESUAIAN_KELUAR', 'nama_jenis' => 'Penyesuaian Keluar', 'arah_stok' => 'KELUAR']);

        $peran = Peran::create(['id' => 1, 'nama_peran' => 'Pemilik']);
        Pengguna::create([
            'id' => 1,
            'peran_id' => $peran->id,
            'nama' => 'Pemilik Test',
            'email' => 'pemilik-split@bbc.com',
            'kata_sandi' => Hash::make('password'),
            'status_aktif' => true,
        ]);

        $statusPengadaan = [1 => 'MENUNGGU', 2 => 'DISETUJUI', 3 => 'DITOLAK', 4 => 'SELESAI'];
        foreach ($statusPengadaan as $id => $kode) {
            StatusPengadaan::create(['id' => $id, 'kode_status' => $kode, 'nama_status' => $kode]);
        }

        $bahan = BahanBaku::create([
            'id' => 1,
            'kategori_bahan_baku_id' => $kategori->id,
            'satuan_id' => $satuan->id,
            'kode_bahan' => 'BERAS',
            'nama_bahan' => 'Beras',
            'stok_minimal' => 10,
            'status_aktif' => true,
        ]);

        // Saldo Harian dan Catering terpisah.
        StokBahan::create([
            'bahan_baku_id' => $bahan->id,
            'jenis_persediaan' => StokBahan::JENIS_HARIAN,
            'jumlah_stok' => 100,
            'stok_minimal' => 10,
            'terakhir_diperbarui' => now(),
        ]);
        StokBahan::create([
            'bahan_baku_id' => $bahan->id,
            'jenis_persediaan' => StokBahan::JENIS_CATERING,
            'jumlah_stok' => 0,
            'stok_minimal' => 10,
            'terakhir_diperbarui' => now(),
        ]);
    }

    private function buatPengadaan(string $jenis, int $jumlah): PengadaanBahan
    {
        $pengadaan = PengadaanBahan::create([
            'nomor_pengadaan' => 'PO-'.$jenis.'-'.uniqid(),
            'diajukan_oleh' => 1,
            'status_pengadaan_id' => 2,
            'jenis_pengadaan' => $jenis,
            'tanggal_pengadaan' => now(),
        ]);

        DetailPengadaanBahan::create([
            'pengadaan_bahan_id' => $pengadaan->id,
            'bahan_baku_id' => 1,
            'jumlah_dipesan' => $jumlah,
            'jumlah_diterima' => 0,
            'satuan_id' => 1,
            'harga_satuan' => 10_000,
            'subtotal' => $jumlah * 10_000,
        ]);

        return $pengadaan;
    }

    public function test_penerimaan_pengadaan_harian_tidak_menyentuh_stok_catering(): void
    {
        $manajer = $this->makeManajer();
        $this->seedReferences();

        // Penerimaan PO Harian → stok Harian bertambah, Catering tidak berubah.
        $poHarian = $this->buatPengadaan('harian', 100);
        $this->actingAs($manajer)->post(route('pengadaan.proses-terima', $poHarian->id), [
            'jumlah_aktual' => [$poHarian->detail_pengadaan_bahan->first()->id => 100],
            'harga_aktual' => [$poHarian->detail_pengadaan_bahan->first()->id => 10_000],
        ])->assertRedirect();

        $this->assertEquals(200, (float) StokBahan::harian()->first()->jumlah_stok);
        $this->assertEquals(0, (float) StokBahan::catering()->first()->jumlah_stok);

        // Mutasi tercatat pada jenis persediaan 'harian'.
        $this->assertEquals(1, MutasiStok::where('jenis_persediaan', 'harian')->count());
        $this->assertEquals(0, MutasiStok::where('jenis_persediaan', 'catering')->count());

        // Penerimaan PO Catering → stok Catering bertambah, Harian tidak berubah.
        $poCatering = $this->buatPengadaan('catering', 50);
        $this->actingAs($manajer)->post(route('pengadaan.proses-terima', $poCatering->id), [
            'jumlah_aktual' => [$poCatering->detail_pengadaan_bahan->first()->id => 50],
            'harga_aktual' => [$poCatering->detail_pengadaan_bahan->first()->id => 10_000],
        ])->assertRedirect();

        $this->assertEquals(200, (float) StokBahan::harian()->first()->jumlah_stok);
        $this->assertEquals(50, (float) StokBahan::catering()->first()->jumlah_stok);
        $this->assertEquals(1, MutasiStok::where('jenis_persediaan', 'catering')->count());
    }

    public function test_terima_sebagian_lalu_pelunasan_menandai_po_selesai(): void
    {
        $manajer = $this->makeManajer();
        $this->seedReferences();

        $po = $this->buatPengadaan('harian', 25);
        $detail = $po->detail_pengadaan_bahan->first();

        // Terima sebagian: 20 dari 25.
        $this->actingAs($manajer)->post(route('pengadaan.proses-terima', $po->id), [
            'jumlah_aktual' => [$detail->id => 20],
            'harga_aktual' => [$detail->id => 12_000],
        ])->assertRedirect();

        $po->refresh();
        $this->assertEquals(5, $po->status_pengadaan_id);
        $this->assertEquals(240_000, $po->total_pengadaan);
        $this->assertEquals(120, (float) StokBahan::harian()->first()->jumlah_stok); // 100 + 20

        // PO dengan status Diterima Sebagian masih bisa diterima lagi.
        $this->actingAs($manajer)->get(route('pengadaan.form-terima', $po->id))->assertStatus(200);

        // Pelunasan sisa 5 → status SELESAI (4), total kumulatif benar.
        $this->actingAs($manajer)->post(route('pengadaan.proses-terima', $po->id), [
            'jumlah_aktual' => [$detail->id => 5],
            'harga_aktual' => [$detail->id => 12_000],
        ])->assertRedirect();

        $po->refresh();
        $this->assertEquals(4, $po->status_pengadaan_id);
        $this->assertEquals(125, (float) StokBahan::harian()->first()->jumlah_stok); // 100 + 25

        // Tidak bisa diterima lagi setelah selesai.
        $this->actingAs($manajer)->get(route('pengadaan.form-terima', $po->id))
            ->assertRedirect();
    }

    public function test_penyesuaian_stok_harian_dan_catering_terpisah(): void
    {
        $manajer = $this->makeManajer();
        $this->seedReferences();

        // Opname fisik: Harian = 90, Catering = 70 (dari 100 / 0).
        $this->actingAs($manajer)->post(route('penyesuaian-stok.store'), [
            'alasan' => 'Opname bulanan',
            'bahan_baku_id' => [1, 1],
            'jenis_persediaan' => ['harian', 'catering'],
            'jumlah_fisik' => [90, 70],
            'catatan_item' => [null, null],
        ])->assertRedirect(route('penyesuaian-stok.index'));

        $this->assertEquals(90, (float) StokBahan::harian()->first()->jumlah_stok);
        $this->assertEquals(70, (float) StokBahan::catering()->first()->jumlah_stok);

        // Kartu stok mencatat jenis persediaan masing-masing.
        $mutasiHarian = MutasiStok::where('jenis_persediaan', 'harian')->first();
        $mutasiCatering = MutasiStok::where('jenis_persediaan', 'catering')->first();
        $this->assertNotNull($mutasiHarian);
        $this->assertNotNull($mutasiCatering);
        $this->assertEquals(100, (float) $mutasiHarian->stok_sebelum);
        $this->assertEquals(90, (float) $mutasiHarian->stok_sesudah);
        $this->assertEquals(0, (float) $mutasiCatering->stok_sebelum);
        $this->assertEquals(70, (float) $mutasiCatering->stok_sesudah);
    }

    public function test_deduct_stok_satu_jenis_tidak_mempengaruhi_jenis_lain(): void
    {
        $this->seedReferences();
        $service = app(StockService::class);

        $service->deductStock(1, 40, 'Kebutuhan Dine-In', 2, null, [], false, 'harian');
        $this->assertEquals(60, (float) StokBahan::harian()->first()->jumlah_stok);
        $this->assertEquals(0, (float) StokBahan::catering()->first()->jumlah_stok);

        // Stok Catering tidak cukup → error, dan stok Harian tidak tersentuh.
        try {
            $service->deductStock(1, 30, 'Kebutuhan Catering', 2, null, [], false, 'catering');
            $this->fail('Seharusnya melempar exception karena stok catering tidak cukup.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Catering', $e->getMessage());
        }

        $this->assertEquals(60, (float) StokBahan::harian()->first()->jumlah_stok);
        $this->assertEquals(0, (float) StokBahan::catering()->first()->jumlah_stok);
    }

    public function test_notifikasi_stok_menipis_per_jenis_persediaan(): void
    {
        $this->seedReferences();

        // Harian 100 ≥ min 10 → aman. Catering 0 < min 10 → habis.
        $service = app(StokNotificationService::class);
        $service->checkAndNotify();

        $this->assertEquals(1, NotifikasiStok::count());
        $notif = NotifikasiStok::first();
        $this->assertEquals('catering', $notif->jenis_persediaan);
        $this->assertEquals('habis', $notif->jenis);

        // Harian dibuat menipis → notifikasi terpisah untuk jenis Harian.
        StokBahan::harian()->update(['jumlah_stok' => 3]);
        $service->checkAndNotify();

        $this->assertEquals(2, NotifikasiStok::count());
        $notifHarian = NotifikasiStok::where('jenis_persediaan', 'harian')->first();
        $this->assertNotNull($notifHarian);
        $this->assertEquals('menipis', $notifHarian->jenis);
    }

    public function test_ketersediaan_menu_dual_harian_dan_catering_fr12(): void
    {
        $manajer = $this->makeManajer();
        $this->seedReferences();

        JenisMenu::create(['id' => 1, 'kode_jenis' => 'MAKANAN', 'nama_jenis' => 'Makanan']);
        $menu = Menu::create([
            'jenis_menu_id' => 1,
            'kode_menu' => 'MNU001',
            'nama_menu' => 'Nasi Liwet',
            'harga_jual' => 17_000,
            'status_aktif' => true,
        ]);

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => 1,
            'jumlah' => 100,
            'satuan_id' => 1,
        ]);

        // Harian stok 100 → 1 porsi (menipis). Catering stok 0 → tidak cukup.
        StokBahan::harian()->update(['jumlah_stok' => 100]);
        StokBahan::catering()->update(['jumlah_stok' => 0]);

        $kebutuhan = app(KebutuhanBahanService::class);
        $this->assertEquals(1.0, $kebutuhan->porsiTersedia($menu, 'harian'));
        $this->assertEquals(0.0, $kebutuhan->porsiTersedia($menu, 'catering'));

        $response = $this->actingAs($manajer)->get(route('ketersediaan-menu.index'));
        $response->assertStatus(200);
        $response->assertSee('Nasi Liwet');
        $response->assertViewHas('menus', function ($menus) {
            $item = $menus->getCollection()->first();
            return $item->status_harian === 'Stok Menipis'
                && $item->status_catering === 'Stok Tidak Cukup'
                && $item->porsi_harian === 1.0
                && $item->porsi_catering === 0.0;
        });
    }
}
