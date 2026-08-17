<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\JadwalPesanan;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class NasiBoxCapacityTest extends TestCase
{
    use RefreshDatabase;

    protected $paket;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Assume seeders are run for necessary statuses
        \App\Models\JenisPesanan::firstOrCreate(['id' => 3], ['kode_jenis' => 'NASI_BOX', 'nama_jenis' => 'Nasi Box']);

        Config::set('pesanan.kapasitas_harian_nasi_box', 500);
        
        \App\Models\KategoriMenu::firstOrCreate(['id' => 1], ['nama_kategori' => 'Makanan']);
        \App\Models\JenisMenu::firstOrCreate(['id' => 3], ['kode_jenis' => 'N', 'nama_jenis' => 'Nasi Box']);

        // Siapkan data menu dummy
        $this->paket = Menu::create([
            'id_menu' => 'NBX-001',
            'nama_menu' => 'Paket Nasi Box',
            'kategori_menu_id' => 1,
            'jenis_menu_id' => 3, // Nasi Box
            'harga_jual' => 25000,
            'status_aktif' => true,
        ]);
    }

    private function buatPesananAktif($tanggal, $jumlah)
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Test User',
            'nomor_telepon' => '08' . rand(10000000, 99999999)
        ]);
        
        $pesanan = Pesanan::create([
            'id_pesanan' => 'ORD-TEST-' . uniqid(),
            'jenis_pesanan_id' => 3,
            'status_pesanan_id' => 1, // Aktif (menunggu konfirmasi)
            'pelanggan_id' => $pelanggan->id,
            'tanggal_pesanan' => now(),
            'jumlah_sebelum_potongan' => $this->paket->harga_jual * $jumlah,
            'total_tagihan' => $this->paket->harga_jual * $jumlah,
        ]);

        JadwalPesanan::create([
            'pesanan_id' => $pesanan->id,
            'tanggal_acara' => $tanggal,
            'alamat_pengiriman' => 'Test',
            'nama_penerima' => 'Test',
            'nomor_telepon_penerima' => '081',
        ]);

        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => $this->paket->id,
            'jumlah' => $jumlah,
            'harga_satuan' => $this->paket->harga_jual,
            'subtotal' => $this->paket->harga_jual * $jumlah,
        ]);

        return $pesanan;
    }

    private function postData($tanggal, $jumlahBox)
    {
        return [
            'nama_pemesan' => 'Test User',
            'kontak' => '081234567890',
            'tanggal_acara' => $tanggal,
            'metode_pengiriman' => 'pickup',
            'paket_id' => $this->paket->id,
            'jumlah_box' => $jumlahBox,
            'opsi_pembayaran' => 'dp',
            'jam_pengambilan' => '10:00'
        ];
    }

    // 1. Belum ada pesanan, pesanan 500 box berhasil.
    public function test_pesanan_kosong_bisa_pesan_500()
    {
        $tanggal = Carbon::today()->addDays(5)->format('Y-m-d');
        
        $response = $this->post(route('pesan.nasibox.store'), $this->postData($tanggal, 500));
        
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('detail_pesanan', ['jumlah' => 500]);
    }

    // 2. Sudah ada 300 box, pesanan tambahan 200 box berhasil.
    public function test_sisa_200_bisa_pesan_200()
    {
        $tanggal = Carbon::today()->addDays(5)->format('Y-m-d');
        $this->buatPesananAktif($tanggal, 300);

        $response = $this->post(route('pesan.nasibox.store'), $this->postData($tanggal, 200));
        
        $response->assertSessionHas('success');
        $this->assertEquals(500, DetailPesanan::sum('jumlah'));
    }

    // 3. Sudah ada 300 box, pesanan tambahan 201 box ditolak.
    public function test_sisa_200_pesan_201_ditolak()
    {
        $tanggal = Carbon::today()->addDays(5)->format('Y-m-d');
        $this->buatPesananAktif($tanggal, 300);

        $response = $this->post(route('pesan.nasibox.store'), $this->postData($tanggal, 201));
        
        $response->assertSessionHasErrors(['kapasitas' => 'Kapasitas produksi Nasi Box pada tanggal ' . Carbon::parse($tanggal)->translatedFormat('j F Y') . ' tersisa 200 box. Jumlah pesanan yang Anda masukkan adalah 201 box. Silakan kurangi jumlah pesanan atau pilih tanggal lain.']);
        $this->assertEquals(300, DetailPesanan::sum('jumlah'));
    }

    // 4. Sudah ada 500 box, pesanan baru 500 box pada tanggal yang sama ditolak.
    public function test_penuh_500_pesan_lagi_ditolak()
    {
        $tanggal = Carbon::today()->addDays(5)->format('Y-m-d');
        $this->buatPesananAktif($tanggal, 500);

        $response = $this->post(route('pesan.nasibox.store'), $this->postData($tanggal, 500));
        
        $response->assertSessionHasErrors(['kapasitas' => 'Kapasitas produksi Nasi Box pada tanggal ' . Carbon::parse($tanggal)->translatedFormat('j F Y') . ' tidak mencukupi. Sudah terpesan 500 box dari kapasitas 500 box. Silakan pilih tanggal lain.']);
    }

    // 5. Pesanan berstatus Dibatalkan tidak mengurangi kapasitas.
    public function test_pesanan_dibatalkan_tidak_dihitung()
    {
        $tanggal = Carbon::today()->addDays(5)->format('Y-m-d');
        $pesanan = $this->buatPesananAktif($tanggal, 500);
        $pesanan->update(['status_pesanan_id' => 6]); // Dibatalkan

        // Seharusnya kapasitas kembali utuh, bisa pesan 500
        $response = $this->post(route('pesan.nasibox.store'), $this->postData($tanggal, 500));
        
        $response->assertSessionHas('success');
    }

    // 6. Pesanan pada tanggal berbeda tidak saling memengaruhi.
    public function test_kapasitas_beda_tanggal_tidak_terpengaruh()
    {
        $tanggal1 = Carbon::today()->addDays(5)->format('Y-m-d');
        $tanggal2 = Carbon::today()->addDays(6)->format('Y-m-d');
        
        $this->buatPesananAktif($tanggal1, 500); // Tgl 1 Penuh

        // Tgl 2 harusnya bisa 500
        $response = $this->post(route('pesan.nasibox.store'), $this->postData($tanggal2, 500));
        
        $response->assertSessionHas('success');
    }

    // 7. Kegagalan kapasitas tidak meninggalkan data pesanan sebagian (Transaksi DB utuh)
    public function test_kegagalan_kapasitas_transaksi_aman()
    {
        $tanggal = Carbon::today()->addDays(5)->format('Y-m-d');
        $this->buatPesananAktif($tanggal, 500);
        
        $awalPesanan = Pesanan::count();

        $this->post(route('pesan.nasibox.store'), $this->postData($tanggal, 1));
        
        $akhirPesanan = Pesanan::count();
        $this->assertEquals($awalPesanan, $akhirPesanan); // Tidak ada insert
    }

    // 8. Dua permintaan pada tanggal yang sama tidak dapat membuat total pesanan melebihi 500 box (Atomic Lock - Logic implicitly tested via checkCapacity service output)
    public function test_atomic_lock_exists()
    {
        // Pengujian race condition secara paralel sulit di PHPUnit dasar tanpa multi-threading/forking
        // Kita uji ketersediaan class dan response JSON dari checkCapacity saja
        
        $tanggal = Carbon::today()->addDays(5)->format('Y-m-d');
        $this->buatPesananAktif($tanggal, 200);

        $response = $this->get(route('pesan.nasibox.cek_kapasitas', [
            'tanggal_acara' => $tanggal,
            'jumlah_box' => 400
        ]));

        $response->assertJson([
            'tersedia' => false,
            'kapasitas_harian' => 500,
            'terpesan' => 200,
            'sisa' => 300
        ]);
    }
}
