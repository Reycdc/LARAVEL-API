<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Kursi',
            'Meja',
            'Lemari',
            'Tempat Tidur',
            'Rak Buku',
            'Sofa',
            'Meja Rias',
            'Meja TV',
            'Kitchen Set',
            'Buffet'
        ];

        foreach ($categories as $category) {
            Category::create([
                'name'        => $category,
                'description' => 'Kategori mebel untuk ' . strtolower($category),
            ]);
        }
    }
}
