<?php

namespace App\Listeners;

use App\Events\GameClosed;
use Illuminate\Support\Facades\Log;

class LogGameClosed
{
    public function handle(GameClosed $event): void
    {
        Log::channel('game')->info('Game closed.', $event->context());
    }
}
