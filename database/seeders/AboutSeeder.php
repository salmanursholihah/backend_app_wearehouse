<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AboutUs;

class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
      public function run(): void
    {
        AboutUs::create([
            'app_name' => 'Warehouse Management System',
            'version' => '1.0.0',
            'description' => 'Aplikasi pengelolaan stok dan distribusi barang',
            'developer' => 'Ucta Team',
            'contact' => 'support@warehouse.app',
        ]);
    }
}
