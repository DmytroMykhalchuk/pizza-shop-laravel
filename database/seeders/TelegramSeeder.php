<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TelegramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('telegraph_bots')->insert([
            [
                'token' => env('TELEGRAM_BOT_TOKEN'),
                'name' => 'PizzaShop',
            ],
        ]);
        
        DB::table('telegraph_chats')->insert([
            [
                'chat_id' => env('TELEGRAM_TARGET_USER_ID'),
                'name' => '[private] '.env('TELEGRAM_TARGET_USER_NAME'),
                'telegraph_bot_id' => 1,
            ],
        ]);
    }
}
