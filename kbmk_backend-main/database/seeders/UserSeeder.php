<?php

namespace Database\Seeders;

use App\Models\API\Division;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = Division::all()->keyBy('nama');

        $users = [
            [
                'username' => 'superadmin',
                'email' => 'superadmin@gmail.com',
                'role' => 'superadmin',
                'division_nama' => null,
            ],
            [
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'division_nama' => null,
            ],
            [
                'username' => 'koordivmedia',
                'email' => 'koordivmedia@gmail.com',
                'role' => 'koor_divisi',
                'division_nama' => 'Media',
            ],
            [
                'username' => 'koordivperlengkapan',
                'email' => 'koordivperlengkapan@gmail.com',
                'role' => 'koor_divisi',
                'division_nama' => 'Perlengkapan',
            ]
        ];

        foreach ($users as $userData) {
            $divisionId = $userData['division_nama'] ? $divisions->get($userData['division_nama'])->id : null;

            User::create([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'role' => $userData['role'],
                'division_id' => $divisionId,
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
