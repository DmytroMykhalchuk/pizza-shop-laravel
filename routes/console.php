<?php

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('register-commands',function(){
    $bot=TelegraphBot::find(1);

    $bot->registerCommands([
        'hello'=>'Hello world!',
        'test'=>'Just test',
    ])->send();
    dd($bot);
});