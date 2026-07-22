<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure Roles exist
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Administrator Sistem']);
        $rolePemilik = Role::firstOrCreate(['name' => 'Pemilik'], ['description' => 'Pemilik Restoran']);
        $roleManajer = Role::firstOrCreate(['name' => 'Manajer'], ['description' => 'Manajer Operasional']);
        $roleKasir = Role::firstOrCreate(['name' => 'Kasir'], ['description' => 'Kasir Restoran']);
        $roleTimPengantaran = Role::firstOrCreate(['name' => 'Tim Pengantaran'], ['description' => 'Tim Pengantaran Pesanan']);
        $roleTimDapur = Role::firstOrCreate(['name' => 'Tim Dapur'], ['description' => 'Tim Dapur / Produksi']);
        $roleKonsumen = Role::firstOrCreate(['name' => 'Konsumen'], ['description' => 'Pelanggan / Konsumen']);

        // 2. Set all users' passwords to 'password'
        $password = Hash::make('password');
        User::query()->update(['password' => $password]);

        // 3. Create or Update Dummy Users
        User::updateOrCreate(
            ['email' => 'admin@bbc.com'],
            [
                'name' => 'Admin BBC',
                'password' => $password,
                'role_id' => $roleAdmin->id,
                'phone_number' => '08110000001',
            ]
        );

        User::updateOrCreate(
            ['email' => 'manajer@bbc.com'],
            [
                'name' => 'Manajer BBC',
                'password' => $password,
                'role_id' => $roleManajer->id,
                'phone_number' => '08110000002',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@bbc.com'],
            [
                'name' => 'Kasir BBC',
                'password' => $password,
                'role_id' => $roleKasir->id,
                'phone_number' => '08110000003',
            ]
        );

        User::updateOrCreate(
            ['email' => 'konsumen@bbc.com'],
            [
                'name' => 'Konsumen Demo',
                'password' => $password,
                'role_id' => $roleKonsumen->id,
                'phone_number' => '08110000004',
            ]
        );

        User::updateOrCreate(
            ['email' => 'pemilik@bbc.com'],
            [
                'name' => 'Pemilik BBC',
                'password' => $password,
                'role_id' => $rolePemilik->id,
                'phone_number' => '08110000005',
            ]
        );

        User::updateOrCreate(
            ['email' => 'pengantaran@bbc.com'],
            [
                'name' => 'Tim Pengantaran BBC',
                'password' => $password,
                'role_id' => $roleTimPengantaran->id,
                'phone_number' => '08110000006',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dapur@bbc.com'],
            [
                'name' => 'Tim Dapur BBC',
                'password' => $password,
                'role_id' => $roleTimDapur->id,
                'phone_number' => '08110000007',
            ]
        );
        
        $this->command->info('Akun dummy berhasil dibuat / di-reset:');
        $this->command->info('- admin@bbc-resto.com (Role: Admin)');
        $this->command->info('- pemilik@bbc-resto.com (Role: Pemilik)');
        $this->command->info('- manajer@bbc-resto.com (Role: Manajer)');
        $this->command->info('- kasir@bbc-resto.com (Role: Kasir)');
        $this->command->info('- pengantaran@bbc-resto.com (Role: Tim Pengantaran)');
        $this->command->info('- dapur@bbc-resto.com (Role: Tim Dapur)');
        $this->command->info('- konsumen@bbc-resto.com (Role: Konsumen)');
        $this->command->info('Password untuk semua akun di sistem telah diubah menjadi: password');
    }
}
