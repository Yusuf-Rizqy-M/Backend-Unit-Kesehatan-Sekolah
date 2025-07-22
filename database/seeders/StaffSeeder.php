<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff; // Pastikan model Staff sudah ada

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $baseUrl = 'https://api-uks.rplrus.com/storage/staff_images/';

        $staff = [
            [
                'name' => 'Dr. Hj. Sugiarti Egie. MM',
                'role' => 'Dokter Umum',
                'image' => $baseUrl . 'buegi.png',
                'wa'    => '+62 856-2696-959'
            ],
            [
                'name' => 'Bu Tatik',
                'role' => 'Staff UKS',
                'image' => $baseUrl . 'butatik.png',
                'wa'    => '+62 811-2750-846'
            ],
        ];

        foreach ($staff as $data) {
            Staff::create([
                'name' => $data['name'],
                'role' => $data['role'],
                'image' => $data['image'],
                'wa' => $data['wa']
            ]);
        }
    }
}
