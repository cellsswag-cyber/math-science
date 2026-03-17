<?php

namespace App\Events;

use App\Models\Result;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResultDeclared implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Result $result,
    ) {}

    public function context(): array
    {
        return [
            'result_id' => $this->result->id,
            'game_id' => $this->result->game_id,
            'winning_number' => $this->result->winning_number,
            'declared_at' => optional($this->result->declared_at)->toDateTimeString(),
        ];
    }
}
