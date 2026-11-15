<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categoryIds = DB::table('categories')->pluck('id', 'name');

        $products = [
            ['Tenda & Shelter', 'Tenda Dome 2 Orang', 'both', 850000, 60000, 300000, 10, 6],
            ['Tenda & Shelter', 'Tenda Dome 4 Orang', 'both', 1450000, 90000, 500000, 6, 4],
            ['Tenda & Shelter', 'Flysheet 3x4 Waterproof', 'sale', 275000, 0, 0, 25, 0],
            ['Tenda & Shelter', 'Hammock Camping Parasut', 'both', 185000, 20000, 75000, 15, 8],

            ['Tas & Carrier', 'Carrier 60L', 'both', 950000, 50000, 300000, 8, 6],
            ['Tas & Carrier', 'Daypack 25L', 'sale', 350000, 0, 0, 20, 0],
            ['Tas & Carrier', 'Carrier 80L Expedition', 'rent', 0, 70000, 400000, 0, 4],
            ['Tas & Carrier', 'Dry Bag 20L', 'sale', 120000, 0, 0, 30, 0],

            ['Peralatan Masak', 'Kompor Portable Windproof', 'both', 150000, 15000, 50000, 20, 10],
            ['Peralatan Masak', 'Nesting Cookset 4 Pcs', 'both', 220000, 20000, 75000, 12, 8],
            ['Peralatan Masak', 'Gas Kaleng Butane Isi 3', 'sale', 45000, 0, 0, 100, 0],
            ['Peralatan Masak', 'Water Filter Portable', 'rent', 0, 25000, 100000, 0, 5],

            ['Pakaian & Sepatu', 'Jaket Gunung Waterproof', 'both', 650000, 40000, 250000, 10, 6],
            ['Pakaian & Sepatu', 'Sepatu Hiking Mid Cut', 'both', 1100000, 55000, 400000, 8, 5],
            ['Pakaian & Sepatu', 'Sarung Tangan Polar', 'sale', 95000, 0, 0, 40, 0],
            ['Pakaian & Sepatu', 'Rain Coat Poncho', 'sale', 85000, 0, 0, 50, 0],

            ['Aksesoris & Navigasi', 'Sleeping Bag Polar', 'both', 275000, 25000, 100000, 15, 10],
            ['Aksesoris & Navigasi', 'Headlamp 300 Lumen', 'both', 175000, 15000, 60000, 20, 10],
            ['Aksesoris & Navigasi', 'Trekking Pole Carbon Sepasang', 'both', 450000, 30000, 150000, 10, 6],
            ['Aksesoris & Navigasi', 'Kompas dan Peta Navigasi Set', 'sale', 130000, 0, 0, 25, 0],
        ];

        foreach ($products as [$category, $name, $mode, $salePrice, $rentPrice, $deposit, $stockSale, $stockRent]) {
            DB::table('products')->insert([
                'category_id' => $categoryIds[$category],
                'name' => $name,
                'slug' => Str::slug($name),
                'image' => null,
                'mode' => $mode,
                'sale_price' => $salePrice,
                'rent_price_per_day' => $rentPrice,
                'deposit' => $deposit,
                'stock_sale' => $stockSale,
                'stock_rent' => $stockRent,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}