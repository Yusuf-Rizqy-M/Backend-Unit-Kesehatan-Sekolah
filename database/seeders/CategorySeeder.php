<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'title' => 'Kesehatan Mental',
                'image' => '/images/categories/kesehatanMental.png',
            ],
            [
                'title' => 'Pencegahan Penyakit',
                'image' => '/images/categories/pencegahanPenyakit.png',
            ],
            [
                'title' => 'Kebersihan Diri',
                'image' => '/images/categories/kebersihanDiri.png',
            ],
            [
                'title' => 'Kesehatan Fisik',
                'image' => '/images/categories/kesehatanFisik.png',
            ],
            [
                'title' => 'Pola Hidup Sehat',
                'image' => '/images/categories/polahidupSehat.png',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'title' => $category['title'],
                'image' => $category['image'],
            ]);
        }
    }
}
