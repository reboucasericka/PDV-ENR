<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $cafes = Category::query()->where('slug', 'cafes')->first();
        $doces = Category::query()->where('slug', 'doces')->first();
        $salgados = Category::query()->where('slug', 'salgados')->first();
        $pratos = Category::query()->where('slug', 'pratos-do-dia')->first();
        $bebidas = Category::query()->where('slug', 'bebidas')->first();

        $products = [
            [
                'sku' => 'CAF-ESP',
                'name' => 'Cafe Expresso',
                'category_id' => $cafes?->id,
                'description' => 'Cafe expresso curto',
                'price' => 6.50,
                'stock' => 100,
                'button_color' => '#0ea5e9',
                'sort_order' => 1,
                'is_favorite' => true,
            ],
            [
                'sku' => 'CAF-CAP',
                'name' => 'Capuccino',
                'category_id' => $cafes?->id,
                'description' => 'Capuccino cremoso',
                'price' => 12.00,
                'stock' => 60,
                'button_color' => '#0284c7',
                'sort_order' => 2,
                'is_favorite' => true,
            ],
            [
                'sku' => 'DOC-BQ',
                'name' => 'Bolo de Chocolate',
                'category_id' => $doces?->id,
                'description' => 'Fatia de bolo de chocolate',
                'price' => 9.50,
                'stock' => 40,
                'button_color' => '#f59e0b',
                'sort_order' => 1,
                'is_favorite' => false,
            ],
            [
                'sku' => 'SAL-PQ',
                'name' => 'Pao de Queijo',
                'category_id' => $salgados?->id,
                'description' => 'Pao de queijo quentinho',
                'price' => 8.00,
                'stock' => 80,
                'button_color' => '#10b981',
                'sort_order' => 1,
                'is_favorite' => false,
            ],
            [
                'sku' => 'PRT-FEI',
                'name' => 'Feijoada',
                'category_id' => $pratos?->id,
                'description' => 'Prato do dia',
                'price' => 14.90,
                'stock' => 25,
                'button_color' => '#ef4444',
                'sort_order' => 1,
                'is_favorite' => false,
            ],
            [
                'sku' => 'BEB-SUM',
                'name' => 'Sumo Natural',
                'category_id' => $bebidas?->id,
                'description' => 'Sumo de laranja natural',
                'price' => 5.50,
                'stock' => 50,
                'button_color' => '#8b5cf6',
                'sort_order' => 1,
                'is_favorite' => false,
            ],
        ];

        foreach ($products as $product) {
            if (! $product['category_id']) {
                continue;
            }

            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    ...$product,
                    'image' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
