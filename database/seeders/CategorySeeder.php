<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Concerts', 'slug' => 'concerts'],
            ['name' => 'Sports', 'slug' => 'sports'],
            ['name' => 'Theatre', 'slug' => 'theatre'],
            ['name' => 'Comedy', 'slug' => 'comedy'],
            ['name' => 'Festivals', 'slug' => 'festivals'],
            ['name' => 'Conferences', 'slug' => 'conferences'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
