<?php

namespace App\Livewire\Games;

use App\Services\EntryService;
use App\Services\GameQueryService;
use App\Services\WalletQueryService;
use Livewire\Component;

class GamePlay extends Component
{
    public int $gameId;
    public ?int $prediction_number = null;
    public string $amount = '';

    public function mount(int $game): void
    {
        $this->gameId = $game;
    }

    public function placeEntry(): void
    {
        $payload = $this->validate([
            'prediction_number' => ['required', 'integer', 'between:0,99'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        app(EntryService::class)->placeEntry((int) auth()->id(), [
            'game_id' => $this->gameId,
            'prediction_number' => (int) $payload['prediction_number'],
            'amount' => $payload['amount'],
        ]);

        $this->reset('amount');
        $this->dispatch('wallet-updated');
        session()->flash('success', 'Your prediction has been placed.');
    }

    public function render()
    {
        return view('livewire.games.game-play', [
            'payload' => app(GameQueryService::class)->getGamePlayData($this->gameId, (int) auth()->id()),
            'wallet' => app(WalletQueryService::class)->getWalletSnapshot((int) auth()->id()),
        ])->layout('layouts.app', [
            'title' => 'Play Game',
        ]);
    }
}
