<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Pizza;
use App\Models\PizzaSize;
use App\Models\Translations\PizzaSizeTranslation;
use App\Models\Translations\PizzaTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PizzaRelativesSeeder extends Seeder
{
    private $ingredients = [
        ['name' => 'Моцарела', 'price' => 2.0, 'weight_per_unit' => 50],
        ['name' => 'Томати', 'price' => 1.5, 'weight_per_unit' => 30],
        ['name' => 'Пепероні', 'price' => 2.5, 'weight_per_unit' => 40],
        ['name' => 'Курка', 'price' => 3.0, 'weight_per_unit' => 60],
        ['name' => 'Базилік', 'price' => 1.0, 'weight_per_unit' => 10],
        ['name' => 'Шинка', 'price' => 2.5, 'weight_per_unit' => 55],
        ['name' => 'Ананас', 'price' => 1.8, 'weight_per_unit' => 45],
        ['name' => 'Горгонзола', 'price' => 3.5, 'weight_per_unit' => 70],
        ['name' => 'Пармезан', 'price' => 3.0, 'weight_per_unit' => 65],
        ['name' => 'Фета', 'price' => 2.8, 'weight_per_unit' => 60],
        ['name' => 'Червона цибуля', 'price' => 1.2, 'weight_per_unit' => 20],
        ['name' => 'Кінза', 'price' => 1.0, 'weight_per_unit' => 15],
    ];

    private $pizzaIngredients = [
        ['pizza_id' => 1, 'ingredient_id' => 1, 'quantity' => 2], // Маргарита: 2 порції моцарели
        ['pizza_id' => 1, 'ingredient_id' => 2, 'quantity' => 3], // Маргарита: 3 порції томатів
        ['pizza_id' => 1, 'ingredient_id' => 5, 'quantity' => 1], // Маргарита: 1 порція базиліку
        ['pizza_id' => 2, 'ingredient_id' => 3, 'quantity' => 2], // Пепероні: 2 порції пепероні
        ['pizza_id' => 2, 'ingredient_id' => 1, 'quantity' => 2], // Пепероні: 2 порції моцарели
        ['pizza_id' => 2, 'ingredient_id' => 2, 'quantity' => 2], // Пепероні: 2 порції томатів
        ['pizza_id' => 3, 'ingredient_id' => 4, 'quantity' => 2], // BBQ Курка: 2 порції курки
        ['pizza_id' => 3, 'ingredient_id' => 11, 'quantity' => 1], // BBQ Курка: 1 порція червоної цибулі
        ['pizza_id' => 3, 'ingredient_id' => 12, 'quantity' => 1], // BBQ Курка: 1 порція кінзи
        ['pizza_id' => 4, 'ingredient_id' => 6, 'quantity' => 2], // Гавайська: 2 порції шинки
        ['pizza_id' => 4, 'ingredient_id' => 7, 'quantity' => 1], // Гавайська: 1 порція ананасів
        ['pizza_id' => 5, 'ingredient_id' => 1, 'quantity' => 2], // 4 Сири: 2 порції моцарели
        ['pizza_id' => 5, 'ingredient_id' => 7, 'quantity' => 1], // 4 Сири: 1 порція ананасів
        ['pizza_id' => 5, 'ingredient_id' => 8, 'quantity' => 1], // 4 Сири: 1 порція горгонзоли
        ['pizza_id' => 5, 'ingredient_id' => 9, 'quantity' => 1], // 4 Сири: 1 порція пармезану
        ['pizza_id' => 5, 'ingredient_id' => 10, 'quantity' => 1], // 4 Сири: 1 порція фети
    ];

    private $products = [
        ['name' => 'Кока-Кола 0.5л', 'price' => 1.5],
        ['name' => 'Кока-Кола 1л', 'price' => 2.5],
        ['name' => 'Мінеральна вода 0.5л', 'price' => 1.0],
        ['name' => 'Мінеральна вода 1л', 'price' => 1.8],
        ['name' => 'Часниковий хліб', 'price' => 3.5],
        ['name' => 'Салат Цезар', 'price' => 5.0],
    ];

    private $recommendedProducts = [
        ['pizza_id' => 1, 'product_id' => 1], // Маргарита -> Кока-Кола 0.5л
        ['pizza_id' => 1, 'product_id' => 5], // Маргарита -> Часниковий хліб
        ['pizza_id' => 2, 'product_id' => 1], // Пепероні -> Кока-Кола 0.5л
        ['pizza_id' => 2, 'product_id' => 5], // Пепероні -> Часниковий хліб
        ['pizza_id' => 3, 'product_id' => 2], // BBQ Курка -> Кока-Кола 1л
        ['pizza_id' => 3, 'product_id' => 6], // BBQ Курка -> Салат Цезар
        ['pizza_id' => 4, 'product_id' => 1], // Гавайська -> Кока-Кола 0.5л
        ['pizza_id' => 4, 'product_id' => 5], // Гавайська -> Часниковий хліб
        ['pizza_id' => 5, 'product_id' => 2], // 4 Сири -> Кока-Кола 1л
        ['pizza_id' => 5, 'product_id' => 6], // 4 Сири -> Салат Цезар
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = Language::all();

        // DB::table('ingredients')->insert($this->ingredients);
        // DB::table('pizza_ingredients')->insert($this->pizzaIngredients);
        // DB::table('products')->insert($this->products);
        // DB::table('recommended_products')->insert($this->recommendedProducts);
    }
}
