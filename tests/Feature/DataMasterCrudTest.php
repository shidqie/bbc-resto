<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\DetailPesanan;
use App\Models\ItemPaket;
use App\Models\KategoriBahanBaku;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PilihanItemPaket;
use App\Models\ResepMenu;
use App\Models\Satuan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DataMasterCrudTest extends TestCase
{
    use DatabaseTransactions;

    // ─── HELPER ───────────────────────────────────────────────

    private function pemilik(): \App\Models\Pengguna
    {
        $peran = \App\Models\Peran::firstOrCreate(['nama_peran' => 'Pemilik']);

        return \App\Models\Pengguna::create([
            'nama' => 'Pemilik Test '.uniqid(),
            'email' => 'pemilik-'.uniqid().'@bbc.test',
            'kata_sandi' => Hash::make('password'),
            'peran_id' => $peran->id,
            'nomor_telepon' => '0812'.rand(10000000, 99999999),
            'status_aktif' => true,
        ]);
    }

    private function kategori(): KategoriMenu
    {
        return KategoriMenu::create(['nama_kategori' => 'TEST-KAT '.uniqid(), 'deskripsi' => 'dummy']);
    }

    private function satuan(): Satuan
    {
        return Satuan::create(['nama_satuan' => 'TEST-SAT '.uniqid(), 'singkatan' => 'ts']);
    }

    private function kategoriBahan(): KategoriBahanBaku
    {
        return KategoriBahanBaku::create(['nama_kategori' => 'TEST-KB '.uniqid()]);
    }

    private function bahanBaku(): BahanBaku
    {
        $satuan = $this->satuan();
        $kb = $this->kategoriBahan();

        return BahanBaku::create([
            'kategori_bahan_baku_id' => $kb->id,
            'satuan_id' => $satuan->id,
            'nama_bahan' => 'TEST-BAHAN '.uniqid(),
            'stok_minimal' => 2,
            'jenis_peruntukan' => 'Semua',
            'status_aktif' => true,
        ]);
    }

    private function menuDineIn(): Menu
    {
        return Menu::create([
            'jenis_menu_id' => 1,
            'kategori_menu_id' => $this->kategori()->id,
            'nama_menu' => 'TEST-MENU '.uniqid(),
            'harga_jual' => 25000,
            'status_aktif' => true,
        ]);
    }

    private function mejaTersedia(): Meja
    {
        return Meja::create([
            'nomor_meja' => 'T-'.rand(1000, 9999).uniqid(),
            'kapasitas' => 4,
            'area' => 'Indoor',
            'status_meja_id' => 1,
        ]);
    }

    // ═══ 1. KATEGORI MENU ═════════════════════════════════════

    public function test_kategori_menu_index(): void
    {
        $res = $this->actingAs($this->pemilik())->get(route('kategori-menu.index'));
        $res->assertOk();
    }

    public function test_kategori_menu_store(): void
    {
        $user = $this->pemilik();
        $nama = 'Kategori Test '.uniqid();

        $res = $this->actingAs($user)->post(route('kategori-menu.store'), [
            'nama_kategori' => $nama,
            'deskripsi' => 'dummy',
        ]);

        $res->assertRedirect(route('kategori-menu.index'));
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('kategori_menu', ['nama_kategori' => $nama]);
    }

    public function test_kategori_menu_store_duplicate_ditolak(): void
    {
        $user = $this->pemilik();
        $kategori = $this->kategori();

        $this->actingAs($user)->post(route('kategori-menu.store'), ['nama_kategori' => $kategori->nama_kategori])
            ->assertSessionHasErrors('nama_kategori');
    }

    public function test_kategori_menu_update(): void
    {
        $user = $this->pemilik();
        $kategori = $this->kategori();
        $namaBaru = 'Kategori Updated '.uniqid();

        $this->actingAs($user)->put(route('kategori-menu.update', $kategori->id), [
            'nama_kategori' => $namaBaru,
            'deskripsi' => 'updated',
        ])->assertRedirect(route('kategori-menu.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_menu', ['id' => $kategori->id, 'nama_kategori' => $namaBaru]);
    }

    public function test_kategori_menu_toggle(): void
    {
        $user = $this->pemilik();
        $kategori = $this->kategori();
        $awal = (bool) $kategori->status_aktif;

        $this->actingAs($user)->patch(route('kategori-menu.toggle', $kategori->id))
            ->assertRedirect(route('kategori-menu.index'))->assertSessionHas('success');

        $this->assertNotSame($awal, (bool) $kategori->fresh()->status_aktif);
    }

    public function test_kategori_menu_destroy_gagal_saat_dipakai_menu(): void
    {
        $user = $this->pemilik();
        $kategori = $this->kategori();
        Menu::create([
            'jenis_menu_id' => 1,
            'kategori_menu_id' => $kategori->id,
            'nama_menu' => 'Pakai kategori '.uniqid(),
            'harga_jual' => 1000,
            'status_aktif' => true,
        ]);

        $this->actingAs($user)->delete(route('kategori-menu.destroy', $kategori->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_menu', ['id' => $kategori->id]);
    }

    public function test_kategori_menu_destroy_berhasil(): void
    {
        $user = $this->pemilik();
        $kategori = $this->kategori();

        $this->actingAs($user)->delete(route('kategori-menu.destroy', $kategori->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kategori_menu', ['id' => $kategori->id]);
    }

    // ═══ 2. MENU ══════════════════════════════════════════════

    public function test_menu_index(): void
    {
        $res = $this->actingAs($this->pemilik())->get(route('menu.index'));
        $res->assertOk();
    }

    public function test_menu_create_page(): void
    {
        $res = $this->actingAs($this->pemilik())->get(route('menu.create'));
        $res->assertOk();
    }

    public function test_menu_store(): void
    {
        $user = $this->pemilik();
        $kategori = $this->kategori();
        $nama = 'Menu Test '.uniqid();

        $this->actingAs($user)->post(route('menu.store'), [
            'nama_menu' => $nama,
            'kategori_menu_id' => $kategori->id,
            'jenis_menu_id' => 1,
            'harga_jual' => 35000,
            'status_aktif' => 1,
            'minimal_pemesanan' => 20,
            'deskripsi' => 'dummy',
        ])->assertRedirect(route('menu.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('menu', ['nama_menu' => $nama, 'kategori_menu_id' => $kategori->id]);
    }

    public function test_menu_store_dengan_resep(): void
    {
        $user = $this->pemilik();
        $kategori = $this->kategori();
        $bahan = $this->bahanBaku();
        $nama = 'Menu Resep '.uniqid();

        $this->actingAs($user)->post(route('menu.store'), [
            'nama_menu' => $nama,
            'kategori_menu_id' => $kategori->id,
            'jenis_menu_id' => 1,
            'harga_jual' => 40000,
            'status_aktif' => 1,
            'bahan_baku_id' => [$bahan->id],
            'jumlah_kebutuhan' => [2.5],
            'satuan_id' => [$bahan->satuan_id],
        ])->assertRedirect(route('menu.index'))->assertSessionHas('success');

        $menu = Menu::where('nama_menu', $nama)->first();
        $this->assertNotNull($menu);
        $this->assertDatabaseHas('resep_menu', ['menu_id' => $menu->id, 'bahan_baku_id' => $bahan->id]);
    }

    public function test_menu_store_duplicate_id_tidak_diperlukan(): void
    {
        // id_menu digenerate otomatis oleh model
        $user = $this->pemilik();
        $kategori = $this->kategori();
        $nama = 'Menu Auto ID '.uniqid();

        $this->actingAs($user)->post(route('menu.store'), [
            'nama_menu' => $nama,
            'kategori_menu_id' => $kategori->id,
            'jenis_menu_id' => 1,
            'harga_jual' => 20000,
        ])->assertRedirect(route('menu.index'));

        $menu = Menu::where('nama_menu', $nama)->first();
        $this->assertNotNull($menu->id_menu);
    }

    public function test_menu_store_validation_gagal(): void
    {
        $user = $this->pemilik();

        $this->actingAs($user)->post(route('menu.store'), [
            'nama_menu' => '',
            'harga_jual' => 'abc',
        ])->assertSessionHasErrors(['nama_menu', 'harga_jual']);
    }

    public function test_menu_show(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();

        $this->actingAs($user)->get(route('menu.show', $menu->id))->assertOk();
    }

    public function test_menu_edit(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();

        $this->actingAs($user)->get(route('menu.edit', $menu->id))->assertOk();
    }

    public function test_menu_update(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();
        $namaBaru = 'Menu Updated '.uniqid();

        $this->actingAs($user)->put(route('menu.update', $menu->id), [
            'nama_menu' => $namaBaru,
            'kategori_menu_id' => $menu->kategori_menu_id,
            'jenis_menu_id' => 1,
            'harga_jual' => 99999,
            'status_aktif' => 1,
        ])->assertRedirect(route('menu.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('menu', ['id' => $menu->id, 'nama_menu' => $namaBaru, 'harga_jual' => 99999]);
    }

    public function test_menu_toggle(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();
        $awal = (bool) $menu->status_aktif;

        $this->actingAs($user)->patch(route('menu.toggle', $menu->id))
            ->assertRedirect(route('menu.index'))->assertSessionHas('success');

        $this->assertNotSame($awal, (bool) $menu->fresh()->status_aktif);
    }

    public function test_menu_destroy_gagal_saat_ada_di_pesanan(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();

        $pesanan = Pesanan::create([
            'id_pesanan' => 'TEST-DIN-'.uniqid(),
            'jenis_pesanan_id' => 1,
            'status_pesanan_id' => 1,
            'status_pembayaran_id' => 1,
            'tanggal_pesanan' => now(),
            'total_tagihan' => 25000,
        ]);
        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => $menu->id,
            'jumlah' => 1,
            'harga_satuan' => 25000,
            'subtotal' => 25000,
        ]);

        $this->actingAs($user)->delete(route('menu.destroy', $menu->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('menu', ['id' => $menu->id]);
    }

    public function test_menu_destroy_berhasil(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();

        $this->actingAs($user)->delete(route('menu.destroy', $menu->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('menu', ['id' => $menu->id]);
    }

    public function test_menu_destroy_ikut_menghapus_resep_dan_komponen(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();
        $bahan = $this->bahanBaku();
        ResepMenu::create(['menu_id' => $menu->id, 'bahan_baku_id' => $bahan->id, 'jumlah' => 1, 'satuan_id' => $bahan->satuan_id, 'dikonfirmasi' => true]);
        ItemPaket::create(['menu_id' => $menu->id, 'nama_item' => 'komp', 'tipe_item' => 'tetap', 'urutan' => 1]);

        $this->actingAs($user)->delete(route('menu.destroy', $menu->id))->assertSessionHas('success');

        $this->assertDatabaseMissing('menu', ['id' => $menu->id]);
        $this->assertDatabaseMissing('resep_menu', ['menu_id' => $menu->id]);
        $this->assertDatabaseMissing('item_paket', ['menu_id' => $menu->id]);
    }

    // ═══ 3. RESEP ═════════════════════════════════════════════

    public function test_resep_index(): void
    {
        $res = $this->actingAs($this->pemilik())->get(route('resep.index'));
        $res->assertOk();
    }

    public function test_resep_create_page(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();

        $this->actingAs($user)->get(route('resep.create', $menu->id))->assertOk();
    }

    public function test_resep_store(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();
        $bahan = $this->bahanBaku();

        $this->actingAs($user)->post(route('resep.store', $menu->id), [
            'bahan_baku_id' => [$bahan->id],
            'jumlah_kebutuhan' => [1.5],
            'dikonfirmasi' => 1,
        ])->assertRedirect(route('resep.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('resep_menu', ['menu_id' => $menu->id, 'bahan_baku_id' => $bahan->id]);
    }

    public function test_resep_store_validasi_gagal(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();

        $this->actingAs($user)->post(route('resep.store', $menu->id), [
            'bahan_baku_id' => [],
            'jumlah_kebutuhan' => [],
        ])->assertSessionHasErrors('bahan_baku_id');
    }

    public function test_resep_destroy(): void
    {
        $user = $this->pemilik();
        $menu = $this->menuDineIn();
        $bahan = $this->bahanBaku();
        ResepMenu::create(['menu_id' => $menu->id, 'bahan_baku_id' => $bahan->id, 'jumlah' => 1, 'satuan_id' => $bahan->satuan_id, 'dikonfirmasi' => true]);

        $this->actingAs($user)->delete(route('resep.destroy', $menu->id))
            ->assertRedirect(route('resep.index'))->assertSessionHas('success');

        $this->assertDatabaseMissing('resep_menu', ['menu_id' => $menu->id]);
    }

    public function test_resep_komposisi_store(): void
    {
        $user = $this->pemilik();
        $paket = Menu::create([
            'jenis_menu_id' => 2,
            'kategori_menu_id' => $this->kategori()->id,
            'nama_menu' => 'TEST-PAKET '.uniqid(),
            'harga_jual' => 50000,
            'status_aktif' => true,
        ]);
        $menuTetap = $this->menuDineIn();
        $menuOpsi = $this->menuDineIn();

        $this->actingAs($user)->post(route('resep.komposisi.store', $paket->id), [
            'tetap' => [
                ['menu_id' => $menuTetap->id, 'jumlah' => 1, 'satuan_sajian' => 'porsi'],
            ],
            'kelompok' => [
                [
                    'nama_item' => 'Pilihan Minuman',
                    'minimum_pilihan' => 1,
                    'maksimum_pilihan' => 1,
                    'opsi' => [
                        ['menu_id' => $menuOpsi->id, 'jumlah' => 1, 'satuan_sajian' => 'porsi'],
                    ],
                ],
            ],
        ])->assertRedirect(route('resep.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('item_paket', ['menu_id' => $paket->id, 'tipe_item' => 'tetap']);
        $this->assertDatabaseHas('item_paket', ['menu_id' => $paket->id, 'tipe_item' => 'pilihan']);
        $this->assertDatabaseHas('pilihan_item_paket', ['menu_id' => $menuOpsi->id]);
    }

    public function test_resep_komposisi_kosong_ditolak(): void
    {
        $user = $this->pemilik();
        $paket = Menu::create([
            'jenis_menu_id' => 2,
            'kategori_menu_id' => $this->kategori()->id,
            'nama_menu' => 'TEST-PAKET-EMPTY '.uniqid(),
            'harga_jual' => 50000,
            'status_aktif' => true,
        ]);

        $this->actingAs($user)->post(route('resep.komposisi.store', $paket->id), [])
            ->assertSessionHas('error');
    }

    // ═══ 4. MEJA ══════════════════════════════════════════════

    public function test_meja_index(): void
    {
        $res = $this->actingAs($this->pemilik())->get(route('meja.index'));
        $res->assertOk();
    }

    public function test_meja_store(): void
    {
        $user = $this->pemilik();
        $nomor = 'Meja Test '.uniqid();

        $this->actingAs($user)->post(route('meja.store'), [
            'nomor_meja' => $nomor,
            'kapasitas' => 6,
            'area' => 'Outdoor',
            'status_meja_id' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('meja', ['nomor_meja' => $nomor]);
    }

    public function test_meja_store_duplicate_nomor_ditolak(): void
    {
        $user = $this->pemilik();
        $meja = $this->mejaTersedia();

        $this->actingAs($user)->post(route('meja.store'), [
            'nomor_meja' => $meja->nomor_meja,
            'kapasitas' => 4,
        ])->assertSessionHasErrors('nomor_meja');
    }

    public function test_meja_update(): void
    {
        $user = $this->pemilik();
        $meja = $this->mejaTersedia();
        $nomorBaru = 'Meja Updated '.uniqid();

        $this->actingAs($user)->put(route('meja.update', $meja->id), [
            'nomor_meja' => $nomorBaru,
            'kapasitas' => 8,
            'area' => 'VIP',
            'status_meja_id' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('meja', ['id' => $meja->id, 'nomor_meja' => $nomorBaru, 'kapasitas' => 8]);
    }

    public function test_meja_generate_qr(): void
    {
        $user = $this->pemilik();
        $meja = $this->mejaTersedia();

        $this->actingAs($user)->post(route('meja.generate-qr', $meja->id))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertNotNull($meja->fresh()->qr_token);
    }

    public function test_meja_destroy_gagal_saat_terisi_atau_dipesan(): void
    {
        $user = $this->pemilik();
        $meja = Meja::create([
            'nomor_meja' => 'T-FULL-'.uniqid(),
            'kapasitas' => 4,
            'area' => 'Indoor',
            'status_meja_id' => 2, // TERISI
        ]);

        $this->actingAs($user)->delete(route('meja.destroy', $meja->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('meja', ['id' => $meja->id]);
    }

    public function test_meja_destroy_berhasil_saat_tersedia(): void
    {
        $user = $this->pemilik();
        $meja = $this->mejaTersedia();

        $this->actingAs($user)->delete(route('meja.destroy', $meja->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('meja', ['id' => $meja->id]);
    }

    // ═══ 5. BAHAN BAKU ════════════════════════════════════════

    public function test_bahan_baku_index(): void
    {
        $res = $this->actingAs($this->pemilik())->get(route('bahan-baku.index'));
        $res->assertOk();
    }

    public function test_bahan_baku_store(): void
    {
        $user = $this->pemilik();
        $satuan = $this->satuan();
        $kb = $this->kategoriBahan();
        $nama = 'Bahan Test '.uniqid();

        $this->actingAs($user)->post(route('bahan-baku.store'), [
            'nama_bahan' => $nama,
            'kategori_bahan_baku_id' => $kb->id,
            'satuan_id' => $satuan->id,
            'stok' => 10,
            'stok_minimal_harian' => 3,
            'stok_minimal_catering' => 5,
            'jenis_peruntukan' => 'Semua',
            'status_aktif' => 1,
        ])->assertRedirect(route('bahan-baku.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('bahan_baku', ['nama_bahan' => $nama]);
        $bahan = BahanBaku::where('nama_bahan', $nama)->first();
        $this->assertDatabaseHas('stok_bahan', ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'harian']);
        $this->assertDatabaseHas('stok_bahan', ['bahan_baku_id' => $bahan->id, 'jenis_persediaan' => 'catering']);
    }

    public function test_bahan_baku_store_validasi_gagal(): void
    {
        $user = $this->pemilik();

        $this->actingAs($user)->post(route('bahan-baku.store'), [
            'nama_bahan' => '',
        ])->assertSessionHasErrors(['nama_bahan', 'kategori_bahan_baku_id', 'satuan_id', 'stok_minimal_harian', 'jenis_peruntukan']);
    }

    public function test_bahan_baku_show(): void
    {
        $user = $this->pemilik();
        $bahan = $this->bahanBaku();

        $this->actingAs($user)->get(route('bahan-baku.show', $bahan->id))->assertOk();
    }

    public function test_bahan_baku_drawer(): void
    {
        $user = $this->pemilik();
        $bahan = $this->bahanBaku();

        $this->actingAs($user)->get(route('bahan-baku.drawer', $bahan->id))->assertOk();
    }

    public function test_bahan_baku_update(): void
    {
        $user = $this->pemilik();
        $bahan = $this->bahanBaku();
        $namaBaru = 'Bahan Updated '.uniqid();

        $this->actingAs($user)->put(route('bahan-baku.update', $bahan->id), [
            'nama_bahan' => $namaBaru,
            'kategori_bahan_baku_id' => $bahan->kategori_bahan_baku_id,
            'satuan_id' => $bahan->satuan_id,
            'stok_minimal_harian' => 7,
            'stok_minimal_catering' => 9,
            'jenis_peruntukan' => 'Reguler',
            'status_aktif' => 1,
        ])->assertRedirect(route('bahan-baku.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('bahan_baku', ['id' => $bahan->id, 'nama_bahan' => $namaBaru]);
    }

    public function test_bahan_baku_destroy_gagal_menghapus_namun_menonaktifkan(): void
    {
        $user = $this->pemilik();
        $bahan = $this->bahanBaku();
        $menu = $this->menuDineIn();
        ResepMenu::create(['menu_id' => $menu->id, 'bahan_baku_id' => $bahan->id, 'jumlah' => 1, 'satuan_id' => $bahan->satuan_id, 'dikonfirmasi' => true]);

        $this->actingAs($user)->delete(route('bahan-baku.destroy', $bahan->id))
            ->assertRedirect(route('bahan-baku.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('bahan_baku', ['id' => $bahan->id, 'status_aktif' => 0]);
    }

    public function test_bahan_baku_destroy_berhasil_saat_belum_dipakai(): void
    {
        $user = $this->pemilik();
        $bahan = $this->bahanBaku();

        $this->actingAs($user)->delete(route('bahan-baku.destroy', $bahan->id))
            ->assertRedirect(route('bahan-baku.index'))->assertSessionHas('success');

        $this->assertDatabaseMissing('bahan_baku', ['id' => $bahan->id]);
    }

    // ═══ 6. KATEGORI BAHAN ════════════════════════════════════

    public function test_kategori_bahan_store(): void
    {
        $user = $this->pemilik();
        $nama = 'Kategori Bahan '.uniqid();

        $this->actingAs($user)->post(route('kategori-bahan.store'), ['nama_kategori' => $nama])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_bahan_baku', ['nama_kategori' => $nama]);
    }

    public function test_kategori_bahan_update(): void
    {
        $user = $this->pemilik();
        $kb = $this->kategoriBahan();
        $namaBaru = 'Kategori Bahan Updated '.uniqid();

        $this->actingAs($user)->put(route('kategori-bahan.update', $kb->id), ['nama_kategori' => $namaBaru])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_bahan_baku', ['id' => $kb->id, 'nama_kategori' => $namaBaru]);
    }

    public function test_kategori_bahan_destroy_gagal_saat_dipakai(): void
    {
        $user = $this->pemilik();
        $kb = $this->kategoriBahan();
        $this->bahanBakuDenganKategori($kb);

        $this->actingAs($user)->delete(route('kategori-bahan.destroy', $kb->id))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_bahan_baku', ['id' => $kb->id]);
    }

    public function test_kategori_bahan_destroy_berhasil(): void
    {
        $user = $this->pemilik();
        $kb = $this->kategoriBahan();

        $this->actingAs($user)->delete(route('kategori-bahan.destroy', $kb->id))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('kategori_bahan_baku', ['id' => $kb->id]);
    }

    private function bahanBakuDenganKategori(KategoriBahanBaku $kb): BahanBaku
    {
        $satuan = $this->satuan();

        return BahanBaku::create([
            'kategori_bahan_baku_id' => $kb->id,
            'satuan_id' => $satuan->id,
            'nama_bahan' => 'TEST-BAHAN-KB '.uniqid(),
            'stok_minimal' => 1,
            'jenis_peruntukan' => 'Semua',
            'status_aktif' => true,
        ]);
    }

    // ═══ 7. SATUAN ════════════════════════════════════════════

    public function test_satuan_store(): void
    {
        $user = $this->pemilik();
        $nama = 'Satuan Test '.uniqid();

        $this->actingAs($user)->post(route('satuan.store'), ['nama_satuan' => $nama, 'singkatan' => 'st'])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('satuan', ['nama_satuan' => $nama]);
    }

    public function test_satuan_update(): void
    {
        $user = $this->pemilik();
        $satuan = $this->satuan();
        $namaBaru = 'Satuan Updated '.uniqid();

        $this->actingAs($user)->put(route('satuan.update', $satuan->id), ['nama_satuan' => $namaBaru, 'singkatan' => 'su'])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('satuan', ['id' => $satuan->id, 'nama_satuan' => $namaBaru]);
    }

    public function test_satuan_destroy_gagal_saat_dipakai(): void
    {
        $user = $this->pemilik();
        $satuan = $this->satuan();
        $kb = $this->kategoriBahan();
        BahanBaku::create([
            'kategori_bahan_baku_id' => $kb->id,
            'satuan_id' => $satuan->id,
            'nama_bahan' => 'TEST-BAHAN-SAT '.uniqid(),
            'stok_minimal' => 1,
            'jenis_peruntukan' => 'Semua',
            'status_aktif' => true,
        ]);

        $this->actingAs($user)->delete(route('satuan.destroy', $satuan->id))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('satuan', ['id' => $satuan->id]);
    }

    public function test_satuan_destroy_berhasil(): void
    {
        $user = $this->pemilik();
        $satuan = $this->satuan();

        $this->actingAs($user)->delete(route('satuan.destroy', $satuan->id))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('satuan', ['id' => $satuan->id]);
    }

    public function test_satuan_store_ajax(): void
    {
        $user = $this->pemilik();
        $nama = 'Satuan Ajax '.uniqid();

        $res = $this->actingAs($user)->post(route('satuan.ajax.store'), [
            'nama_satuan' => $nama,
            'singkatan' => 'sj',
        ]);

        $res->assertOk();
        $res->assertJsonFragment(['nama_satuan' => $nama]);
        $this->assertDatabaseHas('satuan', ['nama_satuan' => $nama]);
    }

    // ═══ 8. AKSES ROLE (dummy Pemilik bisa akses; Kasir ditolak) ══

    public function test_kasir_tidak_bisa_akses_data_master(): void
    {
        $peran = \App\Models\Peran::firstOrCreate(['nama_peran' => 'Kasir']);
        $kasir = \App\Models\Pengguna::create([
            'nama' => 'Kasir Test '.uniqid(),
            'email' => 'kasir-'.uniqid().'@bbc.test',
            'kata_sandi' => Hash::make('password'),
            'peran_id' => $peran->id,
            'status_aktif' => true,
        ]);

        $this->actingAs($kasir)->get(route('menu.index'))->assertForbidden();
        $this->actingAs($kasir)->get(route('bahan-baku.index'))->assertForbidden();
        $this->actingAs($kasir)->get(route('meja.index'))->assertForbidden();
    }
}
