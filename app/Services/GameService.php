<?php

namespace App\Services;

use App\Domain\Game\Enums\GameStatus;
use App\Events\GameClosed;
use App\Models\Game;
use App\Repositories\GameRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GameService
{
    public function __construct(
        private readonly GameRepository $games,
        private readonly PlatformCacheService $cache,
    ) {}

    public function createGame(array $payload): Game
    {
        $this->validateSchedule($payload);

        $game = $this->games->createGame([
            'name' => $payload['name'],
            'open_time' => Carbon::parse($payload['open_time']),
            'close_time' => Carbon::parse($payload['close_time']),
            'result_time' => Carbon::parse($payload['result_time']),
            'status' => GameStatus::Pending,
        ]);

        Log::channel('game')->info('Game created.', [
            'game_id' => $game->id,
            'name' => $game->name,
            'status' => $game->status->value,
        ]);

        $this->cache->forgetActiveGames();

        return $game->loadCount('entries');
    }

    public function openGame(int|Game $game): Game
    {
        $game = $this->resolveGame($game);

        if ($game->status === GameStatus::ResultDeclared) {
            throw ValidationException::withMessages([
                'game_id' => 'A game with a declared result cannot be reopened.',
            ]);
        }

        if ($game->status === GameStatus::Open) {
            return $game;
        }

        $game = $this->games->openGame($game);

        Log::channel('game')->info('Game opened.', [
            'game_id' => $game->id,
            'status' => $game->status->value,
        ]);

        $this->cache->forgetActiveGames();

        return $game;
    }

    public function closeGame(int|Game $game): Game
    {
        return DB::transaction(function () use ($game): Game {
            $resolvedGame = $this->resolveGame($game, true);

            if ($resolvedGame->status === GameStatus::ResultDeclared) {
                throw ValidationException::withMessages([
                    'game_id' => 'A game with a declared result cannot be closed again.',
                ]);
            }

            if ($resolvedGame->status === GameStatus::Closed) {
                return $resolvedGame;
            }

            $resolvedGame = $this->games->closeGame($resolvedGame);
            $this->cache->forgetActiveGames();

            event(new GameClosed($resolvedGame));

            return $resolvedGame;
        });
    }

    public function getActiveGames(): Collection
    {
        return $this->games->getActiveGames();
    }

    public function getOpenGames(): Collection
    {
        return $this->games->getOpenGames();
    }

    public function getGamesReadyForResultGeneration(): Collection
    {
        return $this->games->getGamesReadyForResultGeneration();
    }

    public function openDueGames(): int
    {
        $opened = 0;

        foreach ($this->games->getGamesDueToOpen() as $game) {
            if (now()->greaterThanOrEqualTo($game->close_time)) {
                continue;
            }

            $this->openGame($game);
            $opened++;
        }

        return $opened;
    }

    public function closeDueGames(): int
    {
        $closed = 0;

        foreach ($this->games->getGamesDueToClose() as $game) {
            $this->closeGame($game);
            $closed++;
        }

        return $closed;
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

    private function validateSchedule(array $payload): void
    {
        $openTime = Carbon::parse($payload['open_time']);
        $closeTime = Carbon::parse($payload['close_time']);
        $resultTime = Carbon::parse($payload['result_time']);

        if ($openTime->greaterThanOrEqualTo($closeTime)) {
            throw ValidationException::withMessages([
                'close_time' => 'The close time must be later than the open time.',
            ]);
        }

        if ($closeTime->greaterThanOrEqualTo($resultTime)) {
            throw ValidationException::withMessages([
                'result_time' => 'The result time must be later than the close time.',
            ]);
        }
    }
}
