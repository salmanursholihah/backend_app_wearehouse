<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'sku' => 'PRD-001',
                'name' => 'Kabel LAN 10m',
                'description' => 'Kabel LAN Cat6 panjang 10 meter',
                'stock' => 50,
                'unit' => 'pcs',
                'created_by' => 1,
                'status' => 'approved',
                'approved_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'PRD-002',
                'name' => 'Router Mikrotik',
                'description' => 'Router untuk distribusi jaringan',
                'stock' => 15,
                'unit' => 'pcs',
                'created_by' => 1,
                'status' => 'approved',
                'approved_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'PRD-003',
                'name' => 'Switch 24 Port',
                'description' => 'Switch untuk rack server',
                'stock' => 10,
                'unit' => 'pcs',
                'created_by' => 1,
                'status' => 'pending',
                'approved_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
