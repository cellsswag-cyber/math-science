<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WinnerCalculated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, int|float>  $summary
     */
    public function __construct(
        public readonly Game $game,
        public readonly array $summary,
    ) {}

    public function context(): array
    {
        return array_merge($this->summary, [
            'game_id' => $this->game->id,
            'game_name' => $this->game->name,
        ]);
    }
}
