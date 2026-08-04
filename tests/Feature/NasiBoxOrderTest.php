<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\JadwalPesanan;
use App\Models\JenisMenu;
use App\Models\JenisPesanan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\StatusPesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NasiBoxOrderTest extends TestCase
{
    use RefreshDatabase;

    private function seedReferences(): void
    {
        $statuses = [1 => 'MENUNGGU', 2 => 'DIKONFIRMASI', 3 => 'DIPROSES', 4 => 'SIAP', 5 => 'SELESAI', 6 => 'DIBATALKAN'];
        foreach ($statuses as $id => $kode) {
            StatusPesanan::create(['id' => $id, 'kode_status' => $kode, 'nama_status' => $kode]);
        }

        JenisPesanan::create(['id' => 3, 'kode_jenis' => 'NASI_BOX', 'nama_jenis' => 'Nasi Box']);
        JenisMenu::create(['id' => 1, 'kode_jenis' => 'PAKET', 'nama_jenis' => 'Paket']);
    }

    private function makePaket(): Menu
    {
        return Menu::create([
            'jenis_menu_id' => 1,
            'kode_menu' => 'NBX-HEMAT',
            'nama_menu' => 'Nasi Box Hemat',
            'harga_jual' => 17_000,
            'status_aktif' => true,
        ]);
    }

    public function test_pesan_nasi_box_dengan_lokasi_acara_berhasil(): void
    {
        $this->seedReferences();
        $paket = $this->makePaket();

        $response = $this->post(route('pesan.nasibox.store'), [
            'nama_pemesan' => 'Budi Santoso',
            'kontak' => '081234567890',
            'tanggal_acara' => now()->addDays(3)->format('Y-m-d'),
            'lokasi_acara' => 'Jl. Raya Sukabumi No. 1',
            'metode_pengiriman' => 'pickup',
            'paket_id' => $paket->id,
            'jumlah_box' => 10,
            'opsi_pembayaran' => 'dp',
            'catatan' => '',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $pesanan = Pesanan::where('jenis_pesanan_id', 3)->first();
        $this->assertNotNull($pesanan);
        $this->assertEquals(10 * 17_000, (float) $pesanan->total_tagihan);
        $this->assertSame('Jl. Raya Sukabumi No. 1', $pesanan->jadwal_pesanan->alamat_pengantaran);
        $this->assertSame('Nasi Box Hemat', $pesanan->detail_pesanan->first()->menu->nama_menu);
    }

    public function test_pesan_nasi_box_tanpa_lokasi_acara_gagal_validasi(): void
    {
        $this->seedReferences();
        $paket = $this->makePaket();

        $response = $this->post(route('pesan.nasibox.store'), [
            'nama_pemesan' => 'Budi Santoso',
            'kontak' => '081234567890',
            'tanggal_acara' => now()->addDays(3)->format('Y-m-d'),
            'metode_pengiriman' => 'pickup',
            'paket_id' => $paket->id,
            'jumlah_box' => 10,
            'opsi_pembayaran' => 'dp',
        ]);

        $response->assertSessionHasErrors('lokasi_acara');
    }

    public function test_halaman_pesan_nasi_box_berisi_field_lokasi_acara(): void
    {
        $this->seedReferences();
        $this->makePaket();

        $response = $this->get(route('pesan.nasibox'));

        $response->assertOk();
        $response->assertSee('name="lokasi_acara"', false);
    }
}
