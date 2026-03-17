<?php

namespace App\Listeners;

use App\Events\EntryPlaced;
use Illuminate\Support\Facades\Log;

class LogEntryPlaced
{
    public function handle(EntryPlaced $event): void
    {
        Log::channel('game')->info('Entry placed.', $event->context());
    }
}
