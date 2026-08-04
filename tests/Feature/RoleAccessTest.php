<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\JenisMenu;
use App\Models\JenisPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\Pesanan;
use App\Models\StatusMeja;
use App\Models\StatusPesanan;
use App\Models\StatusTiketDapur;
use App\Models\TiketDapur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function seedRolesAndUsers(): array
    {
        $kasir = Peran::create(['id' => 1, 'nama_peran' => 'Kasir']);
        $pelayan = Peran::create(['id' => 2, 'nama_peran' => 'Pelayan']);

        $userKasir = Pengguna::create([
            'id' => 1, 'peran_id' => $kasir->id, 'nama' => 'Kasir Test',
            'email' => 'kasir@test.test', 'kata_sandi' => bcrypt('password'),
        ]);
        $userPelayan = Pengguna::create([
            'id' => 2, 'peran_id' => $pelayan->id, 'nama' => 'Pelayan Test',
            'email' => 'pelayan@test.test', 'kata_sandi' => bcrypt('password'),
        ]);

        return ['kasir' => $userKasir, 'pelayan' => $userPelayan];
    }

    private function seedReferences(): void
    {
        $statuses = [1 => 'MENUNGGU', 2 => 'DIKONFIRMASI', 3 => 'DIPROSES', 4 => 'SIAP', 5 => 'SELESAI', 6 => 'DIBATALKAN'];
        foreach ($statuses as $id => $kode) {
            StatusPesanan::create(['id' => $id, 'kode_status' => $kode, 'nama_status' => $kode]);
        }

        JenisPesanan::create(['id' => 1, 'kode_jenis' => 'DINE_IN', 'nama_jenis' => 'Dine In / Takeaway']);
        JenisPesanan::create(['id' => 2, 'kode_jenis' => 'CAT', 'nama_jenis' => 'Catering']);
        JenisPesanan::create(['id' => 3, 'kode_jenis' => 'BOX', 'nama_jenis' => 'Nasi Box']);

        JenisMenu::create(['id' => 1, 'kode_jenis' => 'MAKANAN', 'nama_jenis' => 'Makanan']);

        StatusTiketDapur::create(['id' => 1, 'kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu']);
        StatusTiketDapur::create(['id' => 2, 'kode_status' => 'DIPROSES', 'nama_status' => 'Diproses']);
        StatusTiketDapur::create(['id' => 3, 'kode_status' => 'SELESAI', 'nama_status' => 'Selesai']);

        StatusMeja::create(['id' => 1, 'kode_status' => 'TERSEDIA', 'nama_status' => 'Tersedia']);
        StatusMeja::create(['id' => 2, 'kode_status' => 'TERISI', 'nama_status' => 'Terisi']);
    }

    private function makeDineInOrder(): array
    {
        $meja = Meja::create(['id' => 1, 'kode_meja' => 'MJ-001', 'nomor_meja' => 'Meja 01', 'kapasitas' => 4, 'status_meja_id' => 1]);
        $menu = Menu::create([
            'id' => 1, 'jenis_menu_id' => 1, 'kode_menu' => 'MNU001',
            'nama_menu' => 'Nasi Liwet', 'harga_jual' => 17_000, 'status_aktif' => true,
        ]);

        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'DIN-20260802-2001',
            'tanggal_pesanan' => now(),
            'jenis_pesanan_id' => 1,
            'meja_id' => $meja->id,
            'status_pesanan_id' => 1,
            'total_tagihan' => 17_000,
            'catatan' => 'Pemesan: Tamu',
        ]);

        $detail = DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 17_000,
            'subtotal' => 17_000,
        ]);

        $kot = TiketDapur::create([
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

        return ['pesanan' => $pesanan, 'detail' => $detail, 'kot' => $kot, 'meja' => $meja];
    }

    // ── PELAYAN ───────────────────────────────────────────────

    public function test_pelayan_dapat_mengakses_pos_dine_in(): void
    {
        $users = $this->seedRolesAndUsers();

        $response = $this->actingAs($users['pelayan'])->get(route('pos.dinein.index'));

        $response->assertOk();
        $response->assertSee('List Pesanan Dine In', false);
    }

    public function test_pelayan_dapat_toggle_status_sajian(): void
    {
        $users = $this->seedRolesAndUsers();
        $this->seedReferences();
        $order = $this->makeDineInOrder();

        $response = $this->actingAs($users['pelayan'])
            ->patchJson(route('pos.dinein.toggle-sajian', $order['detail']->id));

        $response->assertOk()->assertJson(['success' => true, 'disajikan' => true]);
        $this->assertEquals('disajikan', $order['detail']->fresh()->status_item);

        $this->actingAs($users['pelayan'])
            ->patchJson(route('pos.dinein.toggle-sajian', $order['detail']->id))
            ->assertOk()->assertJson(['success' => true, 'disajikan' => false]);
        $this->assertNull($order['detail']->fresh()->status_item);
    }

    public function test_pelayan_tidak_bisa_akses_rute_kasir(): void
    {
        $users = $this->seedRolesAndUsers();

        $this->actingAs($users['pelayan'])->get(route('pos.dinein.print-qr'))->assertForbidden();
        $this->actingAs($users['pelayan'])->post(route('pos.dinein.store-pos'), [
            'meja_id' => 1, 'nama_konsumen' => 'Tamu', 'jumlah_tamu' => 1,
            'items' => [['menu_id' => 1, 'qty' => 1]],
        ])->assertForbidden();
        $this->actingAs($users['pelayan'])->get(route('pos.dinein.print-dapur', 1))->assertForbidden();
    }

    // ── KASIR ─────────────────────────────────────────────────

    public function test_kasir_hanya_akses_rute_pos_dine_in(): void
    {
        $users = $this->seedRolesAndUsers();

        $this->actingAs($users['kasir'])->get(route('pos.dinein.index'))->assertOk();
    }
}
