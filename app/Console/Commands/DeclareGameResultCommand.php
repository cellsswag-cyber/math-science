<?php

namespace App\Console\Commands;

use App\Services\ResultService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class DeclareGameResultCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:declare-result {game : The game ID} {winningNumber? : Optional winning number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Declare a game result and settle winners.';

    public function handle(ResultService $resultService): int
    {
        try {
            $payload = $resultService->declareResult(
                (int) $this->argument('game'),
                $this->argument('winningNumber') !== null ? (int) $this->argument('winningNumber') : null,
            );
        } catch (ValidationException $exception) {
            $this->error(collect($exception->errors())->flatten()->join(' '));

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Game %d settled with winning number %d. Winners: %d. Payout: %.2f',
            $payload['result']->game_id,
            $payload['result']->winning_number,
            $payload['summary']['winner_count'],
            $payload['summary']['total_payout'],
        ));

        return self::SUCCESS;
    }
}
