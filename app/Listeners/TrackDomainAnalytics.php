<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class TrackDomainAnalytics
{
    public function handle(object $event): void
    {
        if (! method_exists($event, 'context')) {
            return;
        }

        Log::channel('analytics')->info(class_basename($event), $event->context());
    }
}
