<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Продукти', 'slug' => 'products', 'icon' => '🛍', 'color' => '#FF5733', 'is_default' => true],
            ['name' => 'Ресторани', 'slug' => 'restaurants', 'icon' => '🍽', 'color' => '#33FF57', 'is_default' => true],
            ['name' => 'Транспорт', 'slug' => 'transport', 'icon' => '🚘', 'color' => '#3357FF', 'is_default' => true],
            ['name' => 'Розваги', 'slug' => 'entertainment', 'icon' => '🍿', 'color' => '#FF33A8', 'is_default' => true],
            ['name' => 'Оренда', 'slug' => 'rent', 'icon' => '🔑', 'color' => '#A833FF', 'is_default' => true],
            ['name' => 'Комунальні послуги', 'slug' => 'utilities', 'icon' => '⚡', 'color' => '#33FFF6', 'is_default' => true],
            ['name' => 'Медицина', 'slug' => 'medical', 'icon' => '💊', 'color' => '#FF8C33', 'is_default' => true],
            ['name' => 'Освіта', 'slug' => 'education', 'icon' => '📚', 'color' => '#8C33FF', 'is_default' => true],
        ];

        foreach ($categories as $category) {
            Category::query()->create($category);
        }
    }
}
