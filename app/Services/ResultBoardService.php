<?php

namespace App\Services;

use App\Domain\Wallet\Enums\TransactionStatus;
use App\Domain\Wallet\Enums\TransactionType;
use App\Models\Game;
use App\Models\Transaction;
use App\Repositories\ResultRepository;

class ResultBoardService
{
    public function __construct(
        private readonly ResultRepository $results,
        private readonly PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getBoardData(): array
    {
        return [
            'recent_results' => $this->getRecentResults(),
            'game_history' => $this->getGameHistory(),
            'leaderboard' => $this->getLeaderboard(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentResults(int $limit = 10): array
    {
        return $this->cache->rememberRecentResults(function () use ($limit): array {
            return $this->results->getRecentResults($limit)
                ->map(fn ($result): array => [
                    'id' => $result->id,
                    'game_id' => $result->game_id,
                    'game_name' => $result->game?->name,
                    'winning_number' => $result->winning_number,
                    'declared_at' => optional($result->declared_at)->toDateTimeString(),
                    'settled_at' => optional($result->settled_at)->toDateTimeString(),
                ])
                ->all();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getGameHistory(int $limit = 20): array
    {
        return Game::query()
            ->with('result')
            ->whereHas('result')
            ->latest('result_time')
            ->limit($limit)
            ->get()
            ->map(fn (Game $game): array => [
                'id' => $game->id,
                'name' => $game->name,
                'status' => $game->status->value,
                'winning_number' => $game->result?->winning_number,
                'declared_at' => optional($game->result?->declared_at)->toDateTimeString(),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLeaderboard(int $limit = 10): array
    {
        return $this->cache->rememberLeaderboard(function () use ($limit): array {
            return Transaction::query()
                ->with('user:id,name')
                ->select('user_id')
                ->selectRaw('SUM(amount) as total_winnings')
                ->where('type', TransactionType::Winnings->value)
                ->where('status', TransactionStatus::Completed->value)
                ->groupBy('user_id')
                ->orderByDesc('total_winnings')
                ->limit($limit)
                ->get()
                ->map(fn (Transaction $transaction): array => [
                    'user_id' => $transaction->user_id,
                    'user_name' => $transaction->user?->name,
                    'total_winnings' => number_format((float) $transaction->total_winnings, 2, '.', ''),
                ])
                ->all();
        });
    }
}
