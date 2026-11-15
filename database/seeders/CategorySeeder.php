<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            ['name' => 'Tenda & Shelter', 'description' => 'Tenda, flysheet, dan hammock untuk berkemah.'],
            ['name' => 'Tas & Carrier', 'description' => 'Carrier, daypack, dan dry bag untuk membawa perlengkapan.'],
            ['name' => 'Peralatan Masak', 'description' => 'Kompor, cookset, gas, dan filter air portable.'],
            ['name' => 'Pakaian & Sepatu', 'description' => 'Jaket, sepatu, sarung tangan, dan jas hujan outdoor.'],
            ['name' => 'Aksesoris & Navigasi', 'description' => 'Sleeping bag, headlamp, trekking pole, dan alat navigasi.'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}