<?php

namespace App\Repositories;

use App\Models\Result;
use Illuminate\Database\Eloquent\Collection;

class ResultRepository
{
    public function createResult(array $attributes): Result
    {
        return Result::query()->create($attributes);
    }

    public function getGameResult(int $gameId, bool $lock = false): ?Result
    {
        $query = Result::query()->where('game_id', $gameId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function getPendingSettlementResults(): Collection
    {
        return Result::query()
            ->with('game')
            ->whereNull('settled_at')
            ->orderBy('declared_at')
            ->get();
    }

    public function getRecentResults(int $limit = 10): Collection
    {
        return Result::query()
            ->with('game')
            ->latest('declared_at')
            ->limit($limit)
            ->get();
    }

    public function markSettled(Result $result): Result
    {
        $result->forceFill([
            'settled_at' => now(),
        ])->save();

        return $result->refresh();
    }
}
