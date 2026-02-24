<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $roles = ['user', 'admin', 'super_admin'];

        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => $roles[array_rand($roles)],
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'is_active' => fake()->boolean(90), // 90% aktif
                'image' => null,
                'remember_token' => Str::random(10),
            ]);

        }
    }
}
