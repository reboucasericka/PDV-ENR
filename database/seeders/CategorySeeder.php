<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Cafes', 'slug' => 'cafes', 'color' => '#0ea5e9', 'icon' => 'coffee', 'sort_order' => 1],
            ['name' => 'Doces', 'slug' => 'doces', 'color' => '#f59e0b', 'icon' => 'cake', 'sort_order' => 2],
            ['name' => 'Salgados', 'slug' => 'salgados', 'color' => '#10b981', 'icon' => 'bread', 'sort_order' => 3],
            ['name' => 'Pratos do Dia', 'slug' => 'pratos-do-dia', 'color' => '#ef4444', 'icon' => 'dish', 'sort_order' => 4],
            ['name' => 'Bebidas', 'slug' => 'bebidas', 'color' => '#8b5cf6', 'icon' => 'cup', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    ...$category,
                    'is_active' => true,
                ]
            );
        }
    }
}
