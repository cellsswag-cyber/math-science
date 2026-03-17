<?php

namespace App\Listeners;

use App\Events\WinnerCalculated;
use Illuminate\Support\Facades\Log;

class LogWinnerCalculated
{
    public function handle(WinnerCalculated $event): void
    {
        Log::channel('result')->info('Winner calculation completed.', $event->context());
    }
}
