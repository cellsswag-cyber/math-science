<?php

namespace App\Livewire\Games;

use App\Services\GameQueryService;
use Livewire\Component;

class GameTimer extends Component
{
    public string $targetTime;
    public string $context = 'closes_in';

    public function render()
    {
        return view('livewire.games.game-timer', [
            'timer' => app(GameQueryService::class)->formatCountdown($this->targetTime),
            'contextLabel' => match ($this->context) {
                'opens_in' => 'Opens in',
                'result_in' => 'Result in',
                default => 'Closes in',
            },
        ]);
    }
}
