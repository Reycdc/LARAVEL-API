<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $productNames = [
            'Kursi Kayu Jati',
            'Meja Makan Minimalis',
            'Lemari Pakaian 3 Pintu',
            'Tempat Tidur Queen Size',
            'Rak Buku Serbaguna',
            'Sofa L Modern',
            'Meja Rias Putih',
            'Meja TV Minimalis',
            'Kitchen Set Custom',
            'Buffet Ukiran Jepara'
        ];

        $categoryIds = Category::pluck('id')->toArray();

        foreach ($productNames as $name) {
            Product::create([
                'category_id' => $faker->randomElement($categoryIds),
                'name'        => $name,
                'description' => 'Produk ' . strtolower($name) . ' berkualitas tinggi dari bahan kayu pilihan.',
                'stock'       => $faker->numberBetween(5, 50),
                'price'       => $faker->numberBetween(500000, 15000000),
            ]);
        }
    }
}
