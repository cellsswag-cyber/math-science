<?php

namespace App\Livewire\Games;

use App\Services\GameQueryService;
use Livewire\Component;

class GameList extends Component
{
    public function render()
    {
        return view('livewire.games.game-list', [
            'games' => app(GameQueryService::class)->getActiveGamesForUser((int) auth()->id()),
        ])->layout('layouts.app', [
            'title' => 'Games',
        ]);
    }
}
