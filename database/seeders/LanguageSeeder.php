<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    private $languages = [
        ['name' => 'Українська', 'locale' => 'uk',],
        ['name' => 'English', 'locale' => 'en',],
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->languages as $language) {
            Language::factory()->create($language);
        }
    }
}
