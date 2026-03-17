<?php

namespace App\Listeners;

use App\Events\ResultDeclared;
use Illuminate\Support\Facades\Log;

class NotifyResultDeclared
{
    public function handle(ResultDeclared $event): void
    {
        Log::channel('stack')->info('Result declaration notification prepared.', [
            'game_id' => $event->result->game_id,
            'result_id' => $event->result->id,
            'winning_number' => $event->result->winning_number,
        ]);
    }
}
