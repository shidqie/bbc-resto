<?php

namespace Tests\Feature;

use App\Models\ItemPaket;
use App\Models\JenisMenu;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\Pengguna;
use App\Models\Peran;
use App\Models\PilihanItemPaket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MenuEditTest extends TestCase
{
    use RefreshDatabase;

    private function pemilik(): Pengguna
    {
        $peran = Peran::firstOrCreate(['nama_peran' => 'Pemilik']);

        return Pengguna::create([
            'nama' => 'Pemilik Test',
            'email' => 'pemilik-test@bbc.com',
            'kata_sandi' => Hash::make('password'),
            'peran_id' => $peran->id,
            'nomor_telepon' => '08110009999',
            'status_aktif' => true,
        ]);
    }

    private function seedData(): void
    {
        JenisMenu::create(['id' => 1, 'kode_jenis' => 'DINE_IN', 'nama_jenis' => 'Dine In']);
        JenisMenu::create(['id' => 2, 'kode_jenis' => 'CATERING', 'nama_jenis' => 'Catering']);
        JenisMenu::create(['id' => 3, 'kode_jenis' => 'NASI_BOX', 'nama_jenis' => 'Nasi Box']);
        KategoriMenu::create(['id' => 1, 'nama_kategori' => 'Makanan Utama']);
        KategoriMenu::create(['id' => 2, 'nama_kategori' => 'Paket Catering']);

        Menu::create([
            'id' => 1,
            'jenis_menu_id' => 1,
            'kategori_menu_id' => 1,
            'nama_menu' => 'Ayam Bakar Madu',
            'deskripsi' => 'Ayam bakar dengan saus madu',
            'harga_jual' => 25000,
            'status_aktif' => true,
        ]);

        $paket = Menu::create([
            'id' => 2,
            'jenis_menu_id' => 2,
            'kategori_menu_id' => 2,
            'nama_menu' => 'Katering Paket A',
            'deskripsi' => 'Paket prasmanan',
            'harga_jual' => 50000,
            'status_aktif' => true,
        ]);

        $komponen = ItemPaket::create([
            'id' => 1,
            'menu_id' => $paket->id,
            'menu_id_terkait' => null,
            'nama_item' => 'Menu Utama',
            'tipe_item' => 'pilihan',
            'minimum_pilihan' => 1,
            'maksimum_pilihan' => 1,
            'urutan' => 1,
        ]);
        PilihanItemPaket::create([
            'item_paket_id' => $komponen->id,
            'nama_pilihan' => 'Ayam Bakar',
            'urutan' => 1,
        ]);
        ItemPaket::create([
            'id' => 2,
            'menu_id' => $paket->id,
            'menu_id_terkait' => null,
            'nama_item' => 'Minuman',
            'tipe_item' => 'tetap',
            'minimum_pilihan' => 0,
            'maksimum_pilihan' => 0,
            'urutan' => 2,
        ]);
    }

    public function test_edit_menu_page_renders_fields_matching_displayed_data()
    {
        $this->seedData();
        $user = $this->pemilik();

        $menu = Menu::where('jenis_menu_id', 1)->first();
        $this->assertNotNull($menu, 'Seeder menu Dine In tidak ditemukan');

        $res = $this->actingAs($user)->get(route('menu.edit', $menu->id));
        $res->assertOk();
        $res->assertSee($menu->nama_menu, false);
        $res->assertSee($menu->harga_jual, false);
    }

    public function test_update_menu_persists()
    {
        $this->seedData();
        $user = $this->pemilik();

        $menu = Menu::where('jenis_menu_id', 1)->first();
        $this->assertNotNull($menu);

        $res = $this->actingAs($user)->put(route('menu.update', $menu->id), [
            'nama' => 'Menu Edited Test',
            'jenis_menu_id' => '1',
            'kategori_menu_id' => $menu->kategori_menu_id,
            'harga' => 99999,
            'status' => 'tersedia',
            'deskripsi' => 'Edit test',
        ]);

        $res->assertSessionHasNoErrors();
        $menu->refresh();
        $this->assertSame('Menu Edited Test', $menu->nama_menu);
        $this->assertSame(99999.0, (float) $menu->harga_jual);
    }

    public function test_update_paket_persists_komponen()
    {
        $this->seedData();
        $user = $this->pemilik();

        $paket = Menu::whereHas('komponen_paket')->where('jenis_menu_id', 2)->first();
        $this->assertNotNull($paket, 'Paket catering tidak ditemukan');

        $res = $this->actingAs($user)->put(route('paket-catering.update', $paket->id), [
            'nama_paket' => 'Paket Edit Test',
            'jenis_paket' => 'catering',
            'harga' => 75000,
            'deskripsi' => 'Updated',
            'komponen' => [
                [
                    'nama_komponen' => 'Menu Utama',
                    'tipe' => 'choice',
                    'urutan' => 1,
                    'jumlah' => 1,
                    'pilihan' => [1, 2], // array of menu ids
                ],
                [
                    'nama_komponen' => 'Minuman',
                    'tipe' => 'fixed',
                    'urutan' => 2,
                    'jumlah' => 1,
                    'pilihan' => [1], // array of menu ids
                ],
            ],
        ]);

        $res->assertSessionHasNoErrors();
        $paket->refresh();
        $this->assertSame('Paket Edit Test', $paket->nama_menu);
        $this->assertSame(75000.0, (float) $paket->harga_jual);
        $this->assertCount(2, $paket->komponen_paket);
        $this->assertSame(2, $paket->komponen_paket()->where('tipe_item', 'pilihan')->first()->opsi()->count());
    }
}
