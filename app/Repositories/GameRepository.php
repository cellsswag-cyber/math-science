<?php

namespace App\Repositories;

use App\Domain\Game\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

class GameRepository
{
    public function getActiveGames(): Collection
    {
        return Game::query()
            ->with('result')
            ->withCount('entries')
            ->whereIn('status', GameStatus::activeValues())
            ->orderBy('open_time')
            ->get();
    }

    public function getOpenGames(): Collection
    {
        return Game::query()
            ->withCount('entries')
            ->where('status', GameStatus::Open->value)
            ->orderBy('close_time')
            ->get();
    }

    public function createGame(array $attributes): Game
    {
        return Game::query()->create($attributes);
    }

    public function openGame(Game $game): Game
    {
        return $this->updateStatus($game, GameStatus::Open);
    }

    public function closeGame(Game $game): Game
    {
        return $this->updateStatus($game, GameStatus::Closed);
    }

    public function updateStatus(Game $game, GameStatus $status): Game
    {
        $game->forceFill(['status' => $status])->save();

        return $game->refresh();
    }

    public function findById(int $gameId, bool $lock = false): Game
    {
        $query = Game::query();

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($gameId);
    }

    public function getGamesDueToOpen(): Collection
    {
        return Game::query()
            ->where('status', GameStatus::Pending->value)
            ->where('open_time', '<=', now())
            ->orderBy('open_time')
            ->get();
    }

    public function getGamesDueToClose(): Collection
    {
        return Game::query()
            ->whereIn('status', [
                GameStatus::Pending->value,
                GameStatus::Open->value,
            ])
            ->where('close_time', '<=', now())
            ->orderBy('close_time')
            ->get();
    }

    public function getGamesReadyForResultGeneration(): Collection
    {
        return Game::query()
            ->doesntHave('result')
            ->where('status', GameStatus::Closed->value)
            ->where('result_time', '<=', now())
            ->orderBy('result_time')
            ->get();
    }
}
