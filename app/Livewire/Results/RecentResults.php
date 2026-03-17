<?php

namespace App\Livewire\Results;

use App\Services\ResultBoardService;
use Livewire\Component;

class RecentResults extends Component
{
    public function render()
    {
        return view('livewire.results.recent-results', [
            'results' => app(ResultBoardService::class)->getRecentResults(5),
        ]);
    }
}
