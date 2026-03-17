<?php

namespace App\Jobs;

use App\Services\ResultService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeclareGameResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $gameId,
        public readonly ?int $winningNumber = null,
    ) {}

    public function handle(ResultService $resultService): void
    {
        $resultService->declareResult($this->gameId, $this->winningNumber);
    }
}
