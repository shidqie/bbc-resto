<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\DetailPengadaanBahan;
use App\Models\DetailPesanan;
use App\Models\JadwalPesanan;
use App\Models\JenisMenu;
use App\Models\JenisMutasiStok;
use App\Models\JenisPembayaran;
use App\Models\JenisPesanan;
use App\Models\KategoriBahanBaku;
use App\Models\Menu;
use App\Models\MutasiStok;
use App\Models\PengadaanBahan;
use App\Models\Pengantaran;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\Pesanan;
use App\Models\ResepMenu;
use App\Models\Satuan;
use App\Models\StatusPembayaran;
use App\Models\StatusPengantaran;
use App\Models\StatusPesanan;
use App\Models\StatusPengadaan;
use App\Models\StokBahan;
use App\Services\OrderService;
use App\Services\PengadaanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengadaanProduksiTest extends TestCase
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

        JenisPesanan::create(['id' => 1, 'kode_jenis' => 'DINE_IN', 'nama_jenis' => 'Dine In']);
        JenisPesanan::create(['id' => 2, 'kode_jenis' => 'CATERING', 'nama_jenis' => 'Catering']);
        JenisMenu::create(['id' => 1, 'kode_jenis' => 'MAKANAN', 'nama_jenis' => 'Makanan']);

        JenisMutasiStok::create(['id' => 1, 'kode_jenis' => 'MASUK', 'nama_jenis' => 'Masuk', 'arah_stok' => 'MASUK']);
        JenisMutasiStok::create(['id' => 2, 'kode_jenis' => 'KELUAR', 'nama_jenis' => 'Keluar', 'arah_stok' => 'KELUAR']);

        $st = [1 => 'MENUNGGU', 2 => 'DISETUJUI', 3 => 'DITOLAK', 4 => 'SELESAI'];
        foreach ($st as $id => $kode) {
            StatusPengadaan::create(['id' => $id, 'kode_status' => $kode, 'nama_status' => $kode]);
        }

        $sp = [1 => 'DIJADWALKAN', 2 => 'SIAP_DIKIRIM', 3 => 'DALAM_PERJALANAN', 4 => 'DITERIMA', 5 => 'GAGAL_DIKIRIM'];
        foreach ($sp as $id => $kode) {
            StatusPengantaran::firstOrCreate(['id' => $id], ['kode_status' => $kode, 'nama_status' => $kode]);
        }

        $satuan = Satuan::create(['id' => 1, 'nama_satuan' => 'Gram', 'singkatan' => 'g']);
        $kategori = KategoriBahanBaku::create(['id' => 1, 'nama_kategori' => 'Bumbu']);

        Peran::create(['id' => 1, 'nama_peran' => 'Pemilik']);
        Pengguna::create([
            'id' => 1,
            'peran_id' => 1,
            'nama' => 'Pemilik',
            'email' => 'pemilik@test.test',
            'kata_sandi' => bcrypt('password'),
        ]);

        BahanBaku::create([
            'id' => 1,
            'kategori_bahan_baku_id' => $kategori->id,
            'satuan_id' => $satuan->id,
            'kode_bahan' => 'BERAS',
            'nama_bahan' => 'Beras',
            'stok_minimal' => 50,
            'status_aktif' => true,
        ]);

        BahanBaku::create([
            'id' => 2,
            'kategori_bahan_baku_id' => $kategori->id,
            'satuan_id' => $satuan->id,
            'kode_bahan' => 'AYAM',
            'nama_bahan' => 'Ayam',
            'stok_minimal' => 20,
            'status_aktif' => true,
        ]);

        StokBahan::create([
            'bahan_baku_id' => 1,
            'jenis_persediaan' => StokBahan::JENIS_HARIAN,
            'jumlah_stok' => 30,
            'stok_minimal' => 50,
            'terakhir_diperbarui' => now(),
        ]);
        StokBahan::create([
            'bahan_baku_id' => 2,
            'jenis_persediaan' => StokBahan::JENIS_HARIAN,
            'jumlah_stok' => 100,
            'stok_minimal' => 20,
            'terakhir_diperbarui' => now(),
        ]);

        // Saldo Catering (awal 0 untuk bahan; diisi sesuai kebutuhan skenario).
        StokBahan::create([
            'bahan_baku_id' => 1,
            'jenis_persediaan' => StokBahan::JENIS_CATERING,
            'jumlah_stok' => 30,
            'stok_minimal' => 50,
            'terakhir_diperbarui' => now(),
        ]);
        StokBahan::create([
            'bahan_baku_id' => 2,
            'jenis_persediaan' => StokBahan::JENIS_CATERING,
            'jumlah_stok' => 100,
            'stok_minimal' => 20,
            'terakhir_diperbarui' => now(),
        ]);
    }

    private function makeMenu(): Menu
    {
        return Menu::create([
            'id' => 1,
            'jenis_menu_id' => 1,
            'kode_menu' => 'MNU001',
            'nama_menu' => 'Nasi Kotak',
            'harga_jual' => 25_000,
            'status_aktif' => true,
        ]);
    }

    private function makeResep(): void
    {
        ResepMenu::create(['menu_id' => 1, 'bahan_baku_id' => 1, 'jumlah' => 100, 'satuan_id' => 1]);
        ResepMenu::create(['menu_id' => 1, 'bahan_baku_id' => 2, 'jumlah' => 50, 'satuan_id' => 1]);
    }

    private function makeCateringOrder(int $qty): Pesanan
    {
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'CTR-'.uniqid(),
            'tanggal_pesanan' => now(),
            'jenis_pesanan_id' => 2,
            'status_pesanan_id' => 2,
            'total_tagihan' => $qty * 25_000,
        ]);

        JadwalPesanan::create([
            'pesanan_id' => $pesanan->id,
            'tanggal_acara' => now()->addDays(3),
            'alamat_pengantaran' => 'Jl. Test No. 1',
            'nama_penerima' => 'Budi',
            'nomor_telepon_penerima' => '081234567890',
        ]);

        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => 1,
            'jumlah' => $qty,
            'harga_satuan' => 25_000,
            'subtotal' => $qty * 25_000,
        ]);

        return $pesanan;
    }

    public function test_usulan_pengadaan_mengikuti_rumus_fr14(): void
    {
        $this->seedReferences();
        $this->makeMenu();
        $this->makeResep();
        $this->makeCateringOrder(2); // kebutuhan: 200 beras, 100 ayam

        $service = app(PengadaanService::class);
        // Pesanan Catering → usulan Pengadaan Catering (jenis persediaan catering).
        $usulan = $service->usulanGabungan(7, 'catering');

        $beras = $usulan->firstWhere('bahan_baku_id', 1);
        $ayam = $usulan->firstWhere('bahan_baku_id', 2);

        // Rumus: kebutuhan + pengaman - stok - sedang dipesan
        // Beras: 200 + 50 - 30 - 0 = 220
        $this->assertEquals(220, $beras['usulan']);
        $this->assertFalse($beras['cukup']);

        // Ayam: 100 + 20 - 100 - 0 = 20
        $this->assertEquals(20, $ayam['usulan']);

        // Stok yang sudah dipesan di PO yang belum selesai dikurangkan
        $pengadaan = PengadaanBahan::create([
            'nomor_pengadaan' => 'PO-0001',
            'diajukan_oleh' => 1,
            'status_pengadaan_id' => 2,
            'jenis_pengadaan' => 'catering',
            'tanggal_pengadaan' => now(),
        ]);
        DetailPengadaanBahan::create([
            'pengadaan_bahan_id' => $pengadaan->id,
            'bahan_baku_id' => 1,
            'jumlah_dipesan' => 50,
            'jumlah_diterima' => 0,
            'satuan_id' => 1,
            'harga_satuan' => 10_000,
            'subtotal' => 500_000,
        ]);

        $usulan2 = $service->usulanGabungan(7, 'catering');
        $beras2 = $usulan2->firstWhere('bahan_baku_id', 1);
        // 220 - 50 (sedang dipesan) = 170
        $this->assertEquals(170, $beras2['usulan']);
    }

    public function test_potong_stok_saat_produksi_dimulai_fr10(): void
    {
        $this->seedReferences();
        $this->makeMenu();
        $this->makeResep();
        // Set stok Catering cukup: beras 500g, ayam 200g (2 porsi butuh 200 beras + 100 ayam)
        StokBahan::where('bahan_baku_id', 1)->where('jenis_persediaan', StokBahan::JENIS_CATERING)->update(['jumlah_stok' => 500]);
        StokBahan::where('bahan_baku_id', 2)->where('jenis_persediaan', StokBahan::JENIS_CATERING)->update(['jumlah_stok' => 200]);

        $pesanan = $this->makeCateringOrder(2);

        $orderService = app(OrderService::class);
        $orderService->potongStokPesanan($pesanan);

        // Stok Catering berkurang (Dine-In/Nasi Box harian tidak tersentuh)
        $this->assertEquals(300, (float) StokBahan::where('bahan_baku_id', 1)->where('jenis_persediaan', StokBahan::JENIS_CATERING)->value('jumlah_stok'));
        // Ayam: 200 - 100 = 100
        $this->assertEquals(100, (float) StokBahan::where('bahan_baku_id', 2)->where('jenis_persediaan', StokBahan::JENIS_CATERING)->value('jumlah_stok'));

        $detail = $pesanan->detail_pesanan()->first();
        $this->assertNotNull($detail->stock_deducted_at);
        $this->assertEquals(2, MutasiStok::where('detail_pesanan_id', $detail->id)->count());
        $this->assertEquals(2, MutasiStok::where('detail_pesanan_id', $detail->id)->where('jenis_persediaan', 'catering')->count());

        // Idempoten: panggil ulang → tidak memotong lagi
        $orderService->potongStokPesanan($pesanan);
        $this->assertEquals(300, (float) StokBahan::where('bahan_baku_id', 1)->where('jenis_persediaan', StokBahan::JENIS_CATERING)->value('jumlah_stok'));
        $this->assertEquals(2, MutasiStok::where('detail_pesanan_id', $detail->id)->count());
    }

    public function test_transisi_status_pengantaran_fr17(): void
    {
        $this->seedReferences();
        $this->makeMenu();
        $this->makeResep();
        $pesanan = $this->makeCateringOrder(1);

        Pengantaran::create([
            'nomor_pengantaran' => 'ANT-0001',
            'pesanan_id' => $pesanan->id,
            'status_pengantaran_id' => 1,
            'jadwal_pengantaran' => now()->addDays(3),
            'nama_penerima' => 'Budi',
            'nomor_telepon_penerima' => '081234567890',
            'alamat_pengantaran' => 'Jl. Test No. 1',
        ]);

        $this->actingAs(Pengguna::find(1))
            ->patch(route('admin.jadwal-pengantaran.update-pengantaran-status', $pesanan->pengantaran->id), [
                'status_pengantaran_id' => 3,
            ]);

        $pengantaran = $pesanan->pengantaran->fresh();
        $this->assertEquals(3, $pengantaran->status_pengantaran_id);
        $this->assertNotNull($pengantaran->berangkat_pada);

        // Update status pesanan → selesai juga menyinkronkan pengantaran ke DITERIMA
        $this->actingAs(Pengguna::find(1))
            ->patch(route('admin.jadwal.update-status', ['jenis' => 'Catering', 'id' => $pesanan->id]), [
                'status' => 5,
            ]);

        $this->assertEquals(5, $pesanan->fresh()->status_pesanan_id);
        $this->assertEquals(4, $pengantaran->fresh()->status_pengantaran_id);
        $this->assertNotNull($pengantaran->fresh()->diterima_pada);
    }
}
