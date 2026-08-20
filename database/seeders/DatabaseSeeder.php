<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'nama' => 'Pemilik BBC',
                'email' => 'pemilik@bbc.com',
                'nomor_telepon' => '0877-1234-1231',
                'peran_id' => 1,
            ],
            [
                'id' => 2,
                'nama' => 'Manager BBC',
                'email' => 'manager@bbc.com',
                'nomor_telepon' => '0877-1234-1232',
                'peran_id' => 2,
            ],
            [
                'id' => 3,
                'nama' => 'Kasir BBC',
                'email' => 'kasir@bbc.com',
                'nomor_telepon' => '0877-1234-1233',
                'peran_id' => 3,
            ],
            [
                'id' => 4,
                'nama' => 'Dapur BBC',
                'email' => 'dapur@bbc.com',
                'nomor_telepon' => '0877-1234-1234',
                'peran_id' => 5,
            ],
            [
                'id' => 5,
                'nama' => 'Tim Pengantaran BBC',
                'email' => 'pengantaran@bbc.com',
                'nomor_telepon' => '0877-1234-1235',
                'peran_id' => 6,
            ],
        ];

        foreach ($users as $u) {
            DB::table('pengguna')->updateOrInsert(
                ['email' => $u['email']],
                [
                    'nama' => $u['nama'],
                    'nomor_telepon' => $u['nomor_telepon'],
                    'peran_id' => $u['peran_id'],
                    'kata_sandi' => Hash::make('password'),
                    'status_aktif' => 1,
                ]
            );
        }
    }
}
