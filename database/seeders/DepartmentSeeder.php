<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        Department::insert([
            ['name' => 'RPL', 'desc' => 'Rekayasa Perangkat Lunak'],
            ['name' => 'Animasi 3D', 'desc' => 'Animasi Tiga Dimensi'],
            ['name' => 'Animasi 2D', 'desc' => 'Animasi Dua Dimensi'],
            ['name' => 'DKV DG', 'desc' => 'Desain Grafis'],
            ['name' => 'DKV TG', 'desc' => 'Desain Tata Grafis'],
        ]);
    }
}
