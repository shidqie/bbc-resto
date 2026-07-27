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
        $roleTimDapur = Role::firstOrCreate(['name' => 'Tim Dapur'], ['description' => 'Tim Dapur / Produksi']);
        $roleTimPengantaran = Role::firstOrCreate(['name' => 'Tim Pengantaran'], ['description' => 'Tim Pengantaran Pesanan']);
        $roleKonsumen = Role::firstOrCreate(['name' => 'Konsumen'], ['description' => 'Pelanggan / Konsumen']);

        // 2. Wipe existing users (Disable FK checks temporarily to avoid constraint errors)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $password = Hash::make('password');

        // 3. Create New Users
        User::create([
            'name' => 'Admin System',
            'email' => 'admin@bbc.com',
            'password' => $password,
            'role_id' => $roleAdmin->id,
            'phone_number' => '08110000000',
        ]);

        User::create([
            'name' => 'Pemilik BBC',
            'email' => 'pemilik@bbc.com',
            'password' => $password,
            'role_id' => $rolePemilik->id,
            'phone_number' => '08110000001',
        ]);

        User::create([
            'name' => 'Manager BBC',
            'email' => 'manager@bbc.com',
            'password' => $password,
            'role_id' => $roleManajer->id,
            'phone_number' => '08110000002',
        ]);

        User::create([
            'name' => 'Kasir BBC',
            'email' => 'kasir@bbc.com',
            'password' => $password,
            'role_id' => $roleKasir->id,
            'phone_number' => '08110000003',
        ]);

        User::create([
            'name' => 'Tim Dapur BBC',
            'email' => 'dapur@bbc.com',
            'password' => $password,
            'role_id' => $roleTimDapur->id,
            'phone_number' => '08110000004',
        ]);
        
        $this->command->info('Semua data pengguna lama telah dihapus.');
        $this->command->info('Data pengguna baru berhasil dibuat:');
        $this->command->info('- Admin (08110000000)');
        $this->command->info('- Pemilik (08110000001)');
        $this->command->info('- Manager (08110000002)');
        $this->command->info('- Kasir (08110000003)');
        $this->command->info('- Tim Dapur (08110000004)');
        $this->command->info('Password untuk semua akun: password');
    }
}
