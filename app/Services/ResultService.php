<?php

namespace App\Services;

use App\Domain\Entry\Enums\EntryStatus;
use App\Domain\Game\Enums\GameStatus;
use App\Events\ResultDeclared;
use App\Events\WinnerCalculated;
use App\Models\Game;
use App\Models\Result;
use App\Repositories\EntryRepository;
use App\Repositories\GameRepository;
use App\Repositories\ResultRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResultService
{
    public function __construct(
        private readonly ResultRepository $results,
        private readonly GameRepository $games,
        private readonly EntryRepository $entries,
        private readonly GameService $gameService,
        private readonly WalletService $walletService,
        private readonly PlatformCacheService $cache,
    ) {}

    public function generateResult(int|Game $game, ?int $winningNumber = null): Result
    {
        return DB::transaction(function () use ($game, $winningNumber): Result {
            $resolvedGame = $this->ensureGameReadyForResult(
                $this->resolveGame($game, true)
            );

            if ($this->results->getGameResult($resolvedGame->id, true) !== null) {
                throw ValidationException::withMessages([
                    'game_id' => 'A result is already stored for this game.',
                ]);
            }

            $result = $this->results->createResult([
                'game_id' => $resolvedGame->id,
                'winning_number' => $winningNumber ?? random_int(0, 99),
                'declared_at' => now(),
                'settled_at' => null,
            ]);

            $this->games->updateStatus($resolvedGame, GameStatus::ResultDeclared);
            $this->cache->forgetActiveGames();
            $this->cache->forgetRecentResults();

            $result = $result->load('game');

            event(new ResultDeclared($result));

            return $result;
        });
    }

    /**
     * @return array{result: Result, summary: array<string, int|float>}
     */
    public function declareResult(int|Game $game, ?int $winningNumber = null): array
    {
        $result = $this->generateResult($game, $winningNumber);
        $summary = $this->calculateWinners($result->game_id, $result->winning_number);

        return [
            'result' => $result->fresh('game'),
            'summary' => $summary,
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public function calculateWinners(int|Game $game, ?int $winningNumber = null): array
    {
        return DB::transaction(function () use ($game, $winningNumber): array {
            $resolvedGame = $this->resolveGame($game, true);
            $result = $this->results->getGameResult($resolvedGame->id, true);

            if ($result !== null && $result->settled_at !== null) {
                throw ValidationException::withMessages([
                    'game_id' => 'Winners have already been calculated for this game.',
                ]);
            }

            if ($result === null && $winningNumber === null) {
                throw ValidationException::withMessages([
                    'game_id' => 'A declared result is required before calculating winners.',
                ]);
            }

            $winningNumber ??= $result?->winning_number;
            $summary = $this->calculateWinnersForGame($resolvedGame, (int) $winningNumber);

            if ($result !== null) {
                $this->results->markSettled($result);
            }

            $this->cache->forgetActiveGames();
            $this->cache->forgetRecentResults();
            $this->cache->forgetLeaderboard();

            event(new WinnerCalculated($resolvedGame, $summary));

            return $summary;
        });
    }

    public function getPendingSettlementResults(): Collection
    {
        return $this->results->getPendingSettlementResults();
    }

    /**
     * @return array<string, int|float>
     */
    private function calculateWinnersForGame(Game $game, int $winningNumber): array
    {
        $winnerCount = 0;
        $loserCount = 0;
        $totalPayout = 0.0;

        foreach ($this->entries->getGameEntries($game->id) as $entry) {
            if ($entry->status !== EntryStatus::Pending) {
                continue;
            }

            $stake = round((float) $entry->amount, 2);
            $this->walletService->consumeLockedFunds($entry->user_id, $stake);

            if ((int) $entry->prediction_number === $winningNumber) {
                $payout = round($stake * 2, 2);
                $this->walletService->addWinnings(
                    $entry->user_id,
                    $payout,
                    null,
                    [
                        'game_id' => $game->id,
                        'entry_id' => $entry->id,
                    ],
                );

                $this->entries->updateStatus($entry, EntryStatus::Win);
                $winnerCount++;
                $totalPayout += $payout;

                continue;
            }

            $this->entries->updateStatus($entry, EntryStatus::Lose);
            $loserCount++;
        }

        return [
            'winning_number' => $winningNumber,
            'processed_entries' => $winnerCount + $loserCount,
            'winner_count' => $winnerCount,
            'loser_count' => $loserCount,
            'total_payout' => round($totalPayout, 2),
        ];
    }

    private function ensureGameReadyForResult(Game $game): Game
    {
        if ($game->status === GameStatus::ResultDeclared) {
            throw ValidationException::withMessages([
                'game_id' => 'This game already has a declared result.',
            ]);
        }

        if ($game->status === GameStatus::Pending || $game->status === GameStatus::Open) {
            if (now()->lt($game->close_time)) {
                throw ValidationException::withMessages([
                    'game_id' => 'Results can only be declared after the game closes.',
                ]);
            }

            $game = $this->gameService->closeGame($game);
        }

        return $game;
    }

    private function resolveGame(int|Game $game, bool $lock = false): Game
    {
        if ($game instanceof Game) {
            return $lock
                ? $this->games->findById($game->id, true)
                : $game;
        }

        return $this->games->findById($game, $lock);
    }
}
