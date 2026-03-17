<?php

namespace App\Events;

use App\Models\Entry;
use App\Models\Transaction;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntryPlaced implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Entry $entry,
        public readonly Transaction $transaction,
    ) {}

    public function context(): array
    {
        return [
            'entry_id' => $this->entry->id,
            'user_id' => $this->entry->user_id,
            'game_id' => $this->entry->game_id,
            'prediction_number' => $this->entry->prediction_number,
            'amount' => $this->entry->amount,
            'transaction_id' => $this->transaction->id,
        ];
    }
}
