<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\JenisMutasiStok;
use App\Models\KategoriBahanBaku;
use App\Models\MutasiStok;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\Satuan;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengadaanLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function makeRole(string $nama): Peran
    {
        return Peran::create(['nama_peran' => $nama]);
    }

    private function makeUser(string $role): Pengguna
    {
        return Pengguna::create([
            'nama' => 'Test '.$role,
            'email' => strtolower($role).'@bbc.com',
            'kata_sandi' => Hash::make('password'),
            'peran_id' => $this->makeRole($role)->id,
            'status_aktif' => true,
        ]);
    }

    private function seedStatusPengadaan(): void
    {
        StatusPengadaan::create(['id' => 1, 'kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Persetujuan']);
        StatusPengadaan::create(['id' => 2, 'kode_status' => 'DISETUJUI', 'nama_status' => 'Disetujui']);
        StatusPengadaan::create(['id' => 3, 'kode_status' => 'DITOLAK', 'nama_status' => 'Ditolak']);
        StatusPengadaan::create(['id' => 4, 'kode_status' => 'SELESAI', 'nama_status' => 'Selesai']);
    }

    private function makeBahanBaku(string $nama = 'Beras', float $stok = 5, float $min = 10): BahanBaku
    {
        $kategori = KategoriBahanBaku::create(['nama_kategori' => 'Bahan Pokok']);
        $satuan = Satuan::create(['nama_satuan' => 'Kilogram', 'singkatan' => 'kg']);
        $bahan = BahanBaku::create([
            'kategori_bahan_baku_id' => $kategori->id,
            'satuan_id' => $satuan->id,
            'kode_bahan' => 'BB-'.uniqid(),
            'nama_bahan' => $nama,
            'stok_minimal' => $min,
            'status_aktif' => true,
        ]);
        StokBahan::create([
            'bahan_baku_id' => $bahan->id,
            'jenis_persediaan' => StokBahan::JENIS_HARIAN,
            'jumlah_stok' => $stok,
            'stok_minimal' => $min,
            'terakhir_diperbarui' => now(),
        ]);
        StokBahan::create([
            'bahan_baku_id' => $bahan->id,
            'jenis_persediaan' => StokBahan::JENIS_CATERING,
            'jumlah_stok' => 0,
            'stok_minimal' => $min,
            'terakhir_diperbarui' => now(),
        ]);
        JenisMutasiStok::create(['id' => 1, 'kode_jenis' => 'MASUK', 'nama_jenis' => 'Masuk', 'arah_stok' => 'MASUK']);
        JenisMutasiStok::create(['id' => 2, 'kode_jenis' => 'KELUAR', 'nama_jenis' => 'Keluar', 'arah_stok' => 'KELUAR']);

        return $bahan;
    }

    // ─── FASE 5: PENGADAAN ───

    public function test_manajer_can_view_pengadaan_index(): void
    {
        $this->seedStatusPengadaan();
        $manajer = $this->makeUser('Manajer');

        $this->actingAs($manajer)->get(route('pengadaan.index'))->assertStatus(200);
    }

    public function test_kasir_cannot_access_pengadaan(): void
    {
        $kasir = $this->makeUser('Kasir');

        $this->actingAs($kasir)->get(route('pengadaan.index'))->assertForbidden();
    }

    public function test_manajer_can_create_pengadaan(): void
    {
        $this->seedStatusPengadaan();
        $manajer = $this->makeUser('Manajer');
        $bahan = $this->makeBahanBaku();

        $response = $this->actingAs($manajer)->post(route('pengadaan.store'), [
            'tanggal_pengadaan' => now()->format('Y-m-d'),
            'nama_pemasok' => 'PT Beras Sejahtera',
            'catatan' => 'Pengadaan rutin',
            'jenis_pengadaan' => 'harian',
            'bahan_baku_id' => [$bahan->id],
            'jumlah' => [25],
            'harga_satuan' => [0],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengadaan_bahan', ['nama_pemasok' => 'PT Beras Sejahtera']);
        $this->assertDatabaseHas('detail_pengadaan_bahan', [
            'bahan_baku_id' => $bahan->id,
            'jumlah_dipesan' => 25,
        ]);
    }

    public function test_receive_pengadaan_increments_stock_and_completes(): void
    {
        $this->seedStatusPengadaan();
        $manajer = $this->makeUser('Manajer');
        $bahan = $this->makeBahanBaku(stok: 5, min: 10);

        $this->actingAs($manajer)->post(route('pengadaan.store'), [
            'tanggal_pengadaan' => now()->format('Y-m-d'),
            'nama_pemasok' => 'PT Beras Sejahtera',
            'jenis_pengadaan' => 'harian',
            'bahan_baku_id' => [$bahan->id],
            'jumlah' => [25],
        ]);

        $pengadaan = $bahan->stok_bahan_baku; // touch
        $po = \App\Models\PengadaanBahan::where('nama_pemasok', 'PT Beras Sejahtera')->firstOrFail();

        $response = $this->actingAs($manajer)->post(route('pengadaan.proses-terima', $po->id), [
            'jumlah_aktual' => [$po->detail_pengadaan_bahan->first()->id => 20],
            'harga_aktual' => [$po->detail_pengadaan_bahan->first()->id => 12000],
            'catatan' => 'Barang datang sesuai',
        ]);

        $response->assertRedirect();

        // Stok Harian bertambah otomatis: 5 + 20 = 25 (Pengadaan Harian)
        $this->assertDatabaseHas('stok_bahan', ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'harian', 'jumlah_stok' => 25]);

        // Mutasi stok MASUK tercatat
        $this->assertDatabaseHas('mutasi_stok', [
            'bahan_baku_id' => $bahan->id,
            'jenis_mutasi_stok_id' => 1,
            'jumlah' => 20,
        ]);

        // Status PO menjadi Diterima Sebagian (5) karena 20 dari 25 diterima, dan total terisi
        $this->assertDatabaseHas('pengadaan_bahan', ['id' => $po->id, 'status_pengadaan_id' => 5, 'total_pengadaan' => 240000]);
    }

    public function test_received_pengadaan_cannot_be_received_twice(): void
    {
        $this->seedStatusPengadaan();
        $manajer = $this->makeUser('Manajer');
        $bahan = $this->makeBahanBaku();

        $this->actingAs($manajer)->post(route('pengadaan.store'), [
            'tanggal_pengadaan' => now()->format('Y-m-d'),
            'nama_pemasok' => 'Toko Sumber Rejeki',
            'jenis_pengadaan' => 'harian',
            'bahan_baku_id' => [$bahan->id],
            'jumlah' => [10],
        ]);

        $po = \App\Models\PengadaanBahan::where('nama_pemasok', 'Toko Sumber Rejeki')->firstOrFail();
        $detail = $po->detail_pengadaan_bahan->first();

        // Terima pertama
        $this->actingAs($manajer)->post(route('pengadaan.proses-terima', $po->id), [
            'jumlah_aktual' => [$detail->id => 10],
            'harga_aktual' => [$detail->id => 5000],
        ])->assertRedirect();

        // Terima kedua harus ditolak
        $this->actingAs($manajer)->post(route('pengadaan.proses-terima', $po->id), [
            'jumlah_aktual' => [$detail->id => 10],
            'harga_aktual' => [$detail->id => 5000],
        ])->assertRedirect();

        $this->assertEquals(1, MutasiStok::where('bahan_baku_id', $bahan->id)->count());
    }

    public function test_pengadaan_harian_prefills_low_stock_bahan(): void
    {
        $this->seedStatusPengadaan();
        $manajer = $this->makeUser('Manajer');
        $bahan = $this->makeBahanBaku(stok: 3, min: 10);

        $response = $this->actingAs($manajer)->get(route('pengadaan.create', ['tipe' => 'harian']));

        $response->assertStatus(200);
        $response->assertSee($bahan->nama_bahan);
    }

    public function test_stok_menipis_page_renders(): void
    {
        $manajer = $this->makeUser('Manajer');
        $this->makeBahanBaku(stok: 3, min: 10);

        $this->actingAs($manajer)->get(route('stok-menipis.index'))->assertStatus(200)->assertSee('Beras');
    }

    public function test_po_pdf_can_be_downloaded(): void
    {
        $this->seedStatusPengadaan();
        $manajer = $this->makeUser('Manajer');
        $bahan = $this->makeBahanBaku();

        $this->actingAs($manajer)->post(route('pengadaan.store'), [
            'tanggal_pengadaan' => now()->format('Y-m-d'),
            'nama_pemasok' => 'PT Tani Maju',
            'jenis_pengadaan' => 'harian',
            'bahan_baku_id' => [$bahan->id],
            'jumlah' => [15],
        ]);

        $po = \App\Models\PengadaanBahan::where('nama_pemasok', 'PT Tani Maju')->firstOrFail();

        $this->actingAs($manajer)->get(route('pengadaan.pdf', $po->id))
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    // ─── FASE 6: LAPORAN ───

    public function test_pemilik_can_view_all_laporan_pages(): void
    {
        $this->seedStatusPengadaan();
        $pemilik = $this->makeUser('Pemilik');

        $this->actingAs($pemilik)->get(route('laporan.penjualan'))->assertStatus(200);
        $this->actingAs($pemilik)->get(route('laporan.stok'))->assertStatus(200);
        $this->actingAs($pemilik)->get(route('laporan.pengadaan'))->assertStatus(200);
        $this->actingAs($pemilik)->get(route('laporan.menu-terlaris'))->assertStatus(200);
    }

    public function test_manajer_can_view_laporan_pengadaan_with_data(): void
    {
        $this->seedStatusPengadaan();
        $manajer = $this->makeUser('Manajer');
        $bahan = $this->makeBahanBaku();

        $this->actingAs($manajer)->post(route('pengadaan.store'), [
            'tanggal_pengadaan' => now()->format('Y-m-d'),
            'nama_pemasok' => 'PT Laporan Test',
            'jenis_pengadaan' => 'harian',
            'bahan_baku_id' => [$bahan->id],
            'jumlah' => [12],
        ]);

        $this->actingAs($manajer)->get(route('laporan.pengadaan'))->assertStatus(200)->assertSee('PT Laporan Test');
    }

    public function test_kasir_cannot_access_laporan(): void
    {
        $kasir = $this->makeUser('Kasir');

        $this->actingAs($kasir)->get(route('laporan.penjualan'))->assertForbidden();
        $this->actingAs($kasir)->get(route('laporan.stok'))->assertForbidden();
        $this->actingAs($kasir)->get(route('laporan.pengadaan'))->assertForbidden();
        $this->actingAs($kasir)->get(route('laporan.menu-terlaris'))->assertForbidden();
    }

    public function test_laporan_pdf_endpoints_work(): void
    {
        $this->seedStatusPengadaan();
        $pemilik = $this->makeUser('Pemilik');

        $this->actingAs($pemilik)->get(route('laporan.penjualan.cetak'))->assertStatus(200);
        $this->actingAs($pemilik)->get(route('laporan.stok.cetak'))->assertStatus(200);
        $this->actingAs($pemilik)->get(route('laporan.pengadaan.cetak'))->assertStatus(200);
        $this->actingAs($pemilik)->get(route('laporan.menu-terlaris.cetak'))->assertStatus(200);
    }

    public function test_dashboard_renders_for_pemilik(): void
    {
        $this->seedStatusPengadaan();
        $pemilik = $this->makeUser('Pemilik');

        $this->actingAs($pemilik)->get(route('dashboard'))->assertStatus(200);
    }
}
