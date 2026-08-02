<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeRole(string $nama): Peran
    {
        return Peran::create(['nama_peran' => $nama]);
    }

    private function makeUser(string $role, array $attrs = []): Pengguna
    {
        return Pengguna::create(array_merge([
            'nama' => 'Test ' . $role,
            'email' => strtolower($role) . '@bbc.com',
            'kata_sandi' => Hash::make('password'),
            'peran_id' => $this->makeRole($role)->id,
            'status_aktif' => true,
        ], $attrs));
    }

    public function test_pemilik_can_view_users_index(): void
    {
        $pemilik = $this->makeUser('Pemilik');

        $response = $this->actingAs($pemilik)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
    }

    public function test_manajer_can_view_users_index(): void
    {
        $manajer = $this->makeUser('Manajer');

        $response = $this->actingAs($manajer)->get(route('users.index'));

        $response->assertStatus(200);
    }

    public function test_kasir_cannot_access_users_index(): void
    {
        $kasir = $this->makeUser('Kasir');

        $response = $this->actingAs($kasir)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_pemilik_can_create_internal_user(): void
    {
        $pemilik = $this->makeUser('Pemilik');
        $peranKasir = $this->makeRole('Kasir');

        $response = $this->actingAs($pemilik)->post(route('users.store'), [
            'nama' => 'Kasir Baru',
            'email' => 'kasir.baru@bbc.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'nomor_telepon' => '081200000001',
            'peran_id' => $peranKasir->id,
            'status_aktif' => 1,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('pengguna', [
            'email' => 'kasir.baru@bbc.com',
            'peran_id' => $peranKasir->id,
            'status_aktif' => 1,
        ]);
    }

    public function test_create_user_requires_valid_data(): void
    {
        $pemilik = $this->makeUser('Pemilik');

        $response = $this->actingAs($pemilik)->post(route('users.store'), [
            'nama' => '',
            'email' => 'bukan-email',
            'password' => 'pendek',
        ]);

        $response->assertSessionHasErrors(['nama', 'email', 'password', 'peran_id']);
    }

    public function test_pemilik_can_update_user(): void
    {
        $pemilik = $this->makeUser('Pemilik');
        $kasir = $this->makeUser('Kasir');

        $response = $this->actingAs($pemilik)->put(route('users.update', $kasir), [
            'nama' => 'Kasir Update',
            'email' => 'kasir.update@bbc.com',
            'nomor_telepon' => '081300000001',
            'peran_id' => $kasir->peran_id,
            'status_aktif' => 1,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('pengguna', [
            'id' => $kasir->id,
            'nama' => 'Kasir Update',
            'email' => 'kasir.update@bbc.com',
        ]);
    }

    public function test_user_cannot_deactivate_self(): void
    {
        $pemilik = $this->makeUser('Pemilik');

        $response = $this->actingAs($pemilik)->put(route('users.update', $pemilik), [
            'nama' => $pemilik->nama,
            'email' => $pemilik->email,
            'peran_id' => $pemilik->peran_id,
            'status_aktif' => 0,
        ]);

        $response->assertSessionHasErrors('status_aktif');
        $this->assertDatabaseHas('pengguna', ['id' => $pemilik->id, 'status_aktif' => 1]);
    }

    public function test_manajer_cannot_modify_pemilik_or_manajer_user(): void
    {
        $manajer = $this->makeUser('Manajer');
        $pemilik = $this->makeUser('Pemilik');

        $response = $this->actingAs($manajer)->put(route('users.update', $pemilik), [
            'nama' => 'Diubah',
            'email' => $pemilik->email,
            'peran_id' => $pemilik->peran_id,
            'status_aktif' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_pemilik_can_toggle_user_status(): void
    {
        $pemilik = $this->makeUser('Pemilik');
        $kasir = $this->makeUser('Kasir');

        $response = $this->actingAs($pemilik)->patch(route('users.toggle-status', $kasir));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('pengguna', ['id' => $kasir->id, 'status_aktif' => 0]);

        $response2 = $this->actingAs($pemilik)->patch(route('users.toggle-status', $kasir));
        $response2->assertOk();
        $this->assertDatabaseHas('pengguna', ['id' => $kasir->id, 'status_aktif' => 1]);
    }

    public function test_cannot_toggle_own_status(): void
    {
        $pemilik = $this->makeUser('Pemilik');

        $response = $this->actingAs($pemilik)->patch(route('users.toggle-status', $pemilik));

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseHas('pengguna', ['id' => $pemilik->id, 'status_aktif' => 1]);
    }

    public function test_at_least_one_pemilik_remains_active(): void
    {
        $pemilik = $this->makeUser('Pemilik');

        $response = $this->actingAs($pemilik)->patch(route('users.toggle-status', $pemilik));

        $response->assertStatus(403);
        $this->assertDatabaseHas('pengguna', ['id' => $pemilik->id, 'status_aktif' => 1]);
    }

    public function test_pemilik_can_reset_password(): void
    {
        $pemilik = $this->makeUser('Pemilik');
        $kasir = $this->makeUser('Kasir');

        $response = $this->actingAs($pemilik)->post(route('users.reset-password', $kasir), [
            'password' => 'barubaru123',
            'password_confirmation' => 'barubaru123',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertTrue(Hash::check('barubaru123', $kasir->fresh()->kata_sandi));
    }

    public function test_reset_password_requires_confirmation(): void
    {
        $pemilik = $this->makeUser('Pemilik');
        $kasir = $this->makeUser('Kasir');

        $response = $this->actingAs($pemilik)->post(route('users.reset-password', $kasir), [
            'password' => 'barubaru123',
            'password_confirmation' => 'tidak-sama',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_pemilik_can_delete_user(): void
    {
        $pemilik = $this->makeUser('Pemilik');
        $kasir = $this->makeUser('Kasir');

        $response = $this->actingAs($pemilik)->delete(route('users.destroy', $kasir));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('pengguna', ['id' => $kasir->id]);
    }

    public function test_manajer_cannot_delete_user(): void
    {
        $manajer = $this->makeUser('Manajer');
        $kasir = $this->makeUser('Kasir');

        $response = $this->actingAs($manajer)->delete(route('users.destroy', $kasir));

        $response->assertForbidden();
    }

    public function test_cannot_delete_self(): void
    {
        $pemilik = $this->makeUser('Pemilik');

        $response = $this->actingAs($pemilik)->delete(route('users.destroy', $pemilik));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('pengguna', ['id' => $pemilik->id]);
    }

    public function test_kasir_cannot_access_roles_index(): void
    {
        $kasir = $this->makeUser('Kasir');

        $response = $this->actingAs($kasir)->get(route('roles.index'));

        $response->assertForbidden();
    }
}
