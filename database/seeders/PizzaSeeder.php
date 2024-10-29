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

class PizzaSeeder extends Seeder
{
    private $pizzas = [
        [
            'base' => [
                'base_price' => 8.99,
                'image' => 'assets/images/margarita-min.png',
            ],
            'translations' => [
                'uk' => [
                    'name' => 'Маргарита',
                    'description' => 'Класика з моцарелою, томатами та базиліком – ніжний смак Італії! 🍅🧀',
                ],
                'en' => [
                    'name' => 'Margherita',
                    'description' => 'Classic with mozzarella, tomatoes, and basil – a gentle taste of Italy! 🍅🧀',
                ],
            ],
        ],
        [
            'base' => [
                'base_price' => 10.99,
                'image' => 'assets/images/pepperoni-min.png',
            ],
            'translations' => [
                'uk' => [
                    'name' => 'Пепероні',
                    'description' => 'Гарячий смак пепероні та тягуча моцарела – просто вибух! 🌶️🔥',
                ],
                'en' => [
                    'name' => 'Pepperoni',
                    'description' => 'Spicy pepperoni with stretchy mozzarella – an explosion of flavor! 🌶️🔥',
                ],
            ],
        ],
        [
            'base' => [
                'base_price' => 12.99,
                'image' => 'assets/images/bbq_chicken-min.png',
            ],
            'translations' => [
                'uk' => [
                    'name' => 'BBQ Курка',
                    'description' => 'Соковита курка та BBQ соус з ноткою диму – ідеально для гурманів! 🍗🔥',
                ],
                'en' => [
                    'name' => 'BBQ Chicken',
                    'description' => 'Juicy chicken with smoky BBQ sauce – perfect for foodies! 🍗🔥',
                ],
            ],
        ],
        [
            'base' => [
                'base_price' => 11.99,
                'image' => 'assets/images/hawaiian-min.png',
            ],
            'translations' => [
                'uk' => [
                    'name' => 'Гавайська',
                    'description' => 'Екзотичний смак з ананасом та шинкою – рай у кожному шматочку! 🍍🍕',
                ],
                'en' => [
                    'name' => 'Hawaiian',
                    'description' => 'Exotic taste with pineapple and ham – paradise in every bite! 🍍🍕',
                ],
            ],
        ],
        [
            'base' => [
                'base_price' => 13.99,
                'image' => 'assets/images/four_cheese-min.png',
            ],
            'translations' => [
                'uk' => [
                    'name' => '4 Сири',
                    'description' => 'Чотири види сиру, об’єднані в досконалість! 🧀🤩',
                ],
                'en' => [
                    'name' => 'Four Cheese',
                    'description' => 'Four types of cheese, united in perfection! 🧀🤩',
                ],
            ],
        ],
    ];


    private $baseSizes = [
        [
            'base' => [
                'size_code' => 'S',
                'price_multiplier' => 1.0,
                'diameter_cm' => 25,
                'weight_multiplier' => 1,
            ],
            'translations' => [
                'uk' => [
                    'name' => 'Мала',
                ],
                'en' => [
                    'name' => 'Small',
                ],
            ],
        ],
        [
            'base' => [
                'size_code' => 'M',
                'price_multiplier' => 1.5,
                'diameter_cm' => 30,
                'weight_multiplier' => 1,
            ],
            'translations' => [
                'uk' => [
                    'name' => 'Середня',
                ],
                'en' => [
                    'name' => 'Medium',
                ],
            ],
        ],
        [
            'base' => [
                'size_code' => 'L',
                'price_multiplier' => 2.0,
                'diameter_cm' => 35,
                'weight_multiplier' => 1,
            ],
            'translations' => [
                'uk' => [
                    'name' => 'Велика',
                ],
                'en' => [
                    'name' => 'Large',
                ],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = Language::all();

        $this->seedPizzas($languages);

        $pizzas = Pizza::all();
        $this->seedSizes($languages, $pizzas);
    }

    private function seedSizes(Collection $languages, Collection $pizzas): void
    {
        foreach ($pizzas as $pizza) {
            foreach ($this->baseSizes as $baseSize) {
                $base = $baseSize['base'];
                
                $pizzaSize = PizzaSize::create([
                    'pizza_id' => $pizza->id,
                    ...$base,
                ]);
                
                foreach ($languages as $language) {
                    $translations = $baseSize['translations'][$language->locale];
                    PizzaSizeTranslation::create([
                        'pizza_size_id' => $pizzaSize->id,
                        'locale' => $language->locale,
                        ...$translations,
                    ]);
                }
            }
        }
    }

    private function seedPizzas(Collection $languages)
    {
        foreach ($this->pizzas as $pizzaData) {
            $base = $pizzaData['base'];

            $pizza = Pizza::factory()->create($base);
            foreach ($languages as $language) {
                $translations = $pizzaData['translations'][$language->locale];

                $translations['pizza_id'] = $pizza->id;
                $translations['locale'] = $language->locale;


                // $pizzaTranslation = PizzaTranslation::where('pizza_id', $pizza->id)->where('locale', $language->locale)->first();
                // if (!$pizzaTranslation) {
                //     PizzaTranslation::factory()->state($translations)->create();
                // } else {
                    PizzaTranslation::updateOrCreate([
                        'pizza_id' => $pizza->id,
                        'locale'=> $language->locale,
                    ], $translations);
                // }
            }
        }
    }
}
