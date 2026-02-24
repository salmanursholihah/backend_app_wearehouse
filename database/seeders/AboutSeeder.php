<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutUs;

class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AboutUs::updateOrCreate(
            ['app_name' => 'Warehouse Management System'],
            [
                'title' => 'Tentang Aplikasi',
                'description' => 'Aplikasi pengelolaan stok dan distribusi barang',
                'image' => null, // kalau kolom image ada & boleh null
            ]
        );
    }
}
