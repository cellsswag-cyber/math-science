<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\Game;
use App\Models\User;
use App\Models\Wallet;
use Livewire\Component;

class PlatformOverview extends Component
{
    public function render()
    {
        return view('livewire.platform-overview', [
            'userCount' => User::query()->count(),
            'walletCount' => Wallet::query()->count(),
            'gameCount' => Game::query()->count(),
            'entryCount' => Entry::query()->count(),
            'openGameCount' => Game::query()->where('status', 'open')->count(),
        ]);
    }
}
