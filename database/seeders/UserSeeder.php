<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->insert([
            [
                'name' => 'Admin Wayfarer',
                'email' => 'admin@wayfarer1.test',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'phone' => '081200000001',
                'role' => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@wayfarer1.test',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'phone' => '081200000002',
                'role' => 'customer',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'siti@wayfarer1.test',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'phone' => '081200000003',
                'role' => 'customer',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}