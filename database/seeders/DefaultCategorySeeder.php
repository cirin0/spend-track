<?php

namespace Database\Seeders;

use App\Models\DefaultCategory;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Продукти', 'slug' => 'products', 'icon' => '🛍', 'color' => '#FF5733'],
            ['name' => 'Ресторани', 'slug' => 'restaurants', 'icon' => '🍽', 'color' => '#33FF57'],
            ['name' => 'Транспорт', 'slug' => 'transport', 'icon' => '🚘', 'color' => '#3357FF'],
            ['name' => 'Розваги', 'slug' => 'entertainment', 'icon' => '🍿', 'color' => '#FF33A8'],
            ['name' => 'Оренда', 'slug' => 'rent', 'icon' => '🔑', 'color' => '#A833FF'],
            ['name' => 'Комунальні послуги', 'slug' => 'utilities', 'icon' => '⚡', 'color' => '#33FFF6'],
            ['name' => 'Медицина', 'slug' => 'medical', 'icon' => '💊', 'color' => '#FF8C33'],
            ['name' => 'Освіта', 'slug' => 'education', 'icon' => '📚', 'color' => '#8C33FF'],
        ];

        foreach ($categories as $category) {
            DefaultCategory::create($category);
        }
    }
}
