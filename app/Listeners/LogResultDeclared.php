<?php

namespace App\Listeners;

use App\Events\ResultDeclared;
use Illuminate\Support\Facades\Log;

class LogResultDeclared
{
    public function handle(ResultDeclared $event): void
    {
        Log::channel('result')->info('Result declared.', $event->context());
    }
}
