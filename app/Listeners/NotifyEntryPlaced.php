<?php

namespace App\Listeners;

use App\Events\EntryPlaced;
use Illuminate\Support\Facades\Log;

class NotifyEntryPlaced
{
    public function handle(EntryPlaced $event): void
    {
        Log::channel('stack')->info('Entry placement notification prepared.', [
            'user_id' => $event->entry->user_id,
            'entry_id' => $event->entry->id,
            'game_id' => $event->entry->game_id,
        ]);
    }
}
