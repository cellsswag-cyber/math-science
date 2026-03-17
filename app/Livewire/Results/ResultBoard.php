<?php

namespace App\Livewire\Results;

use App\Services\ResultBoardService;
use Livewire\Component;

class ResultBoard extends Component
{
    public function render()
    {
        return view('livewire.results.result-board', [
            'board' => app(ResultBoardService::class)->getBoardData(),
        ])->layout('layouts.app', [
            'title' => 'Results',
        ]);
    }
}
