<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure Roles exist - 7 roles as per requirements
        $roles = [
            'Pemilik' => Peran::firstOrCreate(['nama_peran' => 'Pemilik']),
            'Manajer' => Peran::firstOrCreate(['nama_peran' => 'Manajer']),
            'Kasir' => Peran::firstOrCreate(['nama_peran' => 'Kasir']),
            'Dapur' => Peran::firstOrCreate(['nama_peran' => 'Dapur']),
            'Pengantaran' => Peran::firstOrCreate(['nama_peran' => 'Pengantaran']),
            'Pelanggan' => Peran::firstOrCreate(['nama_peran' => 'Pelanggan']),
        ];

        // 2. Wipe existing users (Disable FK checks temporarily to avoid constraint errors)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Pengguna::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2b. Hapus peran lama yang bukan bagian dari 7 role standar
        $validRoleNames = array_keys($roles);
        Peran::whereNotIn('nama_peran', $validRoleNames)->delete();        $password = Hash::make('password');

        // 3. Create New Users
        Pengguna::create([
            'nama' => 'Pemilik BBC',
            'email' => 'pemilik@bbc.com',
            'kata_sandi' => $password,
            'peran_id' => $roles['Pemilik']->id,
            'nomor_telepon' => '08110000001',
            'status_aktif' => true,
        ]);

        Pengguna::create([
            'nama' => 'Manager BBC',
            'email' => 'manager@bbc.com',
            'kata_sandi' => $password,
            'peran_id' => $roles['Manajer']->id,
            'nomor_telepon' => '08110000002',
            'status_aktif' => true,
        ]);

        Pengguna::create([
            'nama' => 'Kasir BBC',
            'email' => 'kasir@bbc.com',
            'kata_sandi' => $password,
            'peran_id' => $roles['Kasir']->id,
            'nomor_telepon' => '08110000003',
            'status_aktif' => true,
        ]);


        Pengguna::create([
            'nama' => 'Dapur BBC',
            'email' => 'dapur@bbc.com',
            'kata_sandi' => $password,
            'peran_id' => $roles['Dapur']->id,
            'nomor_telepon' => '08110000005',
            'status_aktif' => true,
        ]);

        Pengguna::create([
            'nama' => 'Tim Pengantaran BBC',
            'email' => 'pengantaran@bbc.com',
            'kata_sandi' => $password,
            'peran_id' => $roles['Pengantaran']->id,
            'nomor_telepon' => '08110000006',
            'status_aktif' => true,
        ]);

        $this->command->info('Semua data pengguna lama telah dihapus.');
        $this->command->info('Data pengguna baru berhasil dibuat:');
        $this->command->info('- Pemilik (08110000001)');
        $this->command->info('- Manager (08110000002)');
        $this->command->info('- Kasir (08110000003)');
        $this->command->info('- Dapur (08110000005)');
        $this->command->info('- Pengantaran (08110000006)');
        $this->command->info('Password untuk semua akun: password');
    }
}
