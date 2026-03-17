<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\Game;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserHistoryService
{
    public function getHistory(int $userId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Entry::query()
            ->with(['game', 'game.result'])
            ->where('user_id', $userId);

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (! empty($filters['game_id'])) {
            $query->where('game_id', $filters['game_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()
            ->paginate($perPage)
            ->through(fn (Entry $entry): array => [
                'id' => $entry->id,
                'game_id' => $entry->game_id,
                'game_name' => $entry->game?->name,
                'prediction_number' => $entry->prediction_number,
                'amount' => $entry->amount,
                'status' => $entry->status->value,
                'winning_number' => $entry->game?->result?->winning_number,
                'created_at' => optional($entry->created_at)->toDateTimeString(),
            ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAvailableGames(int $userId): array
    {
        return Game::query()
            ->whereHas('entries', fn ($query) => $query->where('user_id', $userId))
            ->orderBy('name')
            ->get()
            ->map(fn (Game $game): array => [
                'id' => $game->id,
                'name' => $game->name,
            ])
            ->all();
    }
}
