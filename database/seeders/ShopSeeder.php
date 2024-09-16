<?php

namespace Database\Seeders;

use App\Models\ShopCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    private array $categorySeed = [
        [
            'name' => 'Піцца',
            'icon' => 'assets/categories/pizza.png',
            'slug' => 'pizza',
        ],
        [
            'name' => 'Напої',
            'icon' => 'assets/categories/drinks.png',
            'slug' => 'drinks',
        ],
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->categorySeed as $category) {
            ShopCategory::factory()->state($category)->create();
        }
    }
}
