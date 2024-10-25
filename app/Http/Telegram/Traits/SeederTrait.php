<?php

namespace App\Http\Telegram\Traits;

use App\Http\Telegram\Actions\Seeder\SeederAction;

trait SeederTrait
{
    private SeederAction $seederAction;

    public function seed()
    {
        $messageId = $this->data->get('messageId');

        $this->seederAction->setChat($this->chat);
        $this->seederAction->seed($messageId);
    }
}
