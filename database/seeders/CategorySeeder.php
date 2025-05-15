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
                'description' => 'Informasi dan tips untuk menjaga kesehatan mental, mengelola stres, serta meningkatkan kesejahteraan emosional.',
                'image' => '/images/categories/kesehatanMental.png',
            ],
            [
                'title' => 'Pencegahan Penyakit',
                'description' => 'Panduan untuk mencegah berbagai penyakit melalui langkah-langkah proaktif dan gaya hidup sehat.',
                'image' => '/images/categories/pencegahanPenyakit.png',
            ],
            [
                'title' => 'Kebersihan Diri',
                'description' => 'Cara menjaga kebersihan diri agar tetap sehat dan terhindar dari kuman serta infeksi.',
                'image' => '/images/categories/kebersihanDiri.png',
            ],
            [
                'title' => 'Kesehatan Fisik',
                'description' => 'Artikel tentang cara menjaga kebugaran fisik, olahraga, dan nutrisi yang mendukung tubuh sehat.',
                'image' => '/images/categories/kesehatanFisik.png',
            ],
            [
                'title' => 'Pola Hidup Sehat',
                'description' => 'Tips dan kebiasaan sehari-hari untuk menerapkan pola hidup sehat demi kualitas hidup yang lebih baik.',
                'image' => '/images/categories/polahidupSehat.png',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'title' => $category['title'],
                'description' => $category['description'],
                'image' => $category['image'],
            ]);
        }
    }
}