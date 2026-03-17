<?php

namespace App\Services;

use App\Domain\Entry\Enums\EntryStatus;
use App\Domain\Game\Enums\GameStatus;
use App\Events\EntryPlaced;
use App\Models\Entry;
use App\Models\Game;
use App\Repositories\EntryRepository;
use App\Repositories\GameRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EntryService
{
    public function __construct(
        private readonly EntryRepository $entries,
        private readonly GameRepository $games,
        private readonly GameService $gameService,
        private readonly WalletService $walletService,
    ) {}

    public function placeEntry(int $userId, array $payload): Entry
    {
        return DB::transaction(function () use ($userId, $payload): Entry {
            $game = $this->resolveEntryGame(
                $this->games->findById((int) $payload['game_id'], true)
            );

            $reference = (string) Str::uuid();
            $transaction = $this->walletService->lockFunds(
                $userId,
                $payload['amount'],
                $reference,
                [
                    'game_id' => $game->id,
                    'prediction_number' => (int) $payload['prediction_number'],
                ],
            );

            $entry = $this->entries->createEntry([
                'user_id' => $userId,
                'game_id' => $game->id,
                'prediction_number' => (int) $payload['prediction_number'],
                'amount' => round((float) $payload['amount'], 2),
                'status' => EntryStatus::Pending,
            ]);

            event(new EntryPlaced($entry->load(['user', 'game']), $transaction));

            return $entry;
        });
    }

    private function resolveEntryGame(Game $game): Game
    {
        $now = now();

        if ($game->status === GameStatus::ResultDeclared) {
            throw ValidationException::withMessages([
                'game_id' => 'This game already has a declared result.',
            ]);
        }

        if ($now->lt($game->open_time)) {
            throw ValidationException::withMessages([
                'game_id' => 'This game is not open for entries yet.',
            ]);
        }

        if ($game->status === GameStatus::Pending) {
            $game = $this->gameService->openGame($game);
        }

        if ($now->greaterThanOrEqualTo($game->close_time)) {
            if ($game->status !== GameStatus::Closed) {
                $this->gameService->closeGame($game);
            }

            throw ValidationException::withMessages([
                'game_id' => 'This game is already closed for entries.',
            ]);
        }

        if ($game->status !== GameStatus::Open) {
            throw ValidationException::withMessages([
                'game_id' => 'Only open games can accept entries.',
            ]);
        }

        return $game;
    }
}
