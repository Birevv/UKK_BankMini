<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        DB::table('users')->insert([
            [
                'username' => 'admin',
                'nama_petugas' => 'Administrator Utama',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'teller1',
                'nama_petugas' => 'Petugas Loket Teller',
                'password' => Hash::make('teller123'),
                'role' => 'teller',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'spv1',
                'nama_petugas' => 'Kepala Bank Mini',
                'password' => Hash::make('spv123'),
                'role' => 'supervisor',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('nasabah')->insert([
            [
                'no_rekening' => '202601001',
                'nis' => '17763',
                'nama_siswa' => 'Dewi Handayani Nur Halimah',
                'kelas' => 'XI RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'username' => 'Handa',
                'password' => Hash::make('password123'),
                'pin_keamanan' => Hash::make('240808'),
                'saldo' => 50000,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
