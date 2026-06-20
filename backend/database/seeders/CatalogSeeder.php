<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Sut mahsulotlari' => [
                ['Sut 2.5% 1L', 12000, 14000, 'litr'],
                ['Kefir 1L', 13000, null, 'litr'],
                ['Tvorog 5% 500g', 18000, 22000, 'dona'],
                ['Smetana 20% 400g', 16000, null, 'dona'],
            ],
            'Non va shirinliklar' => [
                ['Oq non', 4000, null, 'dona'],
                ['Baton', 5000, null, 'dona'],
                ['Pechenye 300g', 15000, 18000, 'dona'],
            ],
            'Mevalar' => [
                ['Olma', 15000, null, 'kg'],
                ['Banan', 22000, 25000, 'kg'],
                ['Apelsin', 19000, null, 'kg'],
            ],
            'Sabzavotlar' => [
                ['Kartoshka', 6000, null, 'kg'],
                ['Piyoz', 5000, null, 'kg'],
                ['Pomidor', 12000, 15000, 'kg'],
            ],
            'Ichimliklar' => [
                ['Coca-Cola 1.5L', 14000, null, 'dona'],
                ['Suv 1.5L', 4000, null, 'dona'],
                ['Sok 1L', 16000, 19000, 'dona'],
            ],
            'Go\'sht mahsulotlari' => [
                ['Mol go\'shti', 95000, null, 'kg'],
                ['Tovuq go\'shti', 38000, 42000, 'kg'],
            ],
        ];

        $sort = 0;

        foreach ($catalog as $categoryName => $products) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]
            );

            foreach ($products as $i => [$name, $price, $oldPrice, $unit]) {
                Product::updateOrCreate(
                    ['slug' => Str::slug($name).'-'.$category->id],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'price' => $price,
                        'old_price' => $oldPrice,
                        'unit' => $unit,
                        'step' => $unit === 'kg' ? 0.5 : 1,
                        'stock' => 100,
                        'is_active' => true,
                        'is_featured' => $i === 0,
                        'sold_count' => rand(0, 500),
                    ]
                );
            }
        }
    }
}
