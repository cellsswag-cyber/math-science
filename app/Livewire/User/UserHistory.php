<?php

namespace App\Livewire\User;

use App\Services\UserHistoryService;
use Livewire\Component;
use Livewire\WithPagination;

class UserHistory extends Component
{
    use WithPagination;

    public ?string $from_date = null;
    public ?string $to_date = null;
    public ?string $status = null;
    public ?int $game_id = null;

    public function updatedFromDate(): void
    {
        $this->resetPage();
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedGameId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(UserHistoryService::class);

        return view('livewire.user.user-history', [
            'entries' => $service->getHistory((int) auth()->id(), [
                'from_date' => $this->from_date,
                'to_date' => $this->to_date,
                'status' => $this->status,
                'game_id' => $this->game_id,
            ]),
            'games' => $service->getAvailableGames((int) auth()->id()),
        ])->layout('layouts.app', [
            'title' => 'History',
        ]);
    }
}
