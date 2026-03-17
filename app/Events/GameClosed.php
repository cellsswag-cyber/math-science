<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameClosed implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Game $game,
    ) {}

    public function context(): array
    {
        return [
            'game_id' => $this->game->id,
            'name' => $this->game->name,
            'status' => $this->game->status->value,
            'close_time' => optional($this->game->close_time)->toDateTimeString(),
        ];
    }
}
