<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PizzaIngridient>
 */
class PizzaIngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'pizza_id' => Pizza::factory(),
            // 'ingredient_id' => Ingredient::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}
