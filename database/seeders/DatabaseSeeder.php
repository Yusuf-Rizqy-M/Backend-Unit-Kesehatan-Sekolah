<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Tambahkan Admin Default
        User::create([
            'name' => 'Admin_uks',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        // Tambahkan user biasa (opsional)
        User::create([
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => Hash::make('user12345'),
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

