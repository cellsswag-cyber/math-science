<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class PlatformCacheService
{
    public const ACTIVE_GAMES_KEY = 'platform:active-games';
    public const RECENT_RESULTS_KEY = 'platform:recent-results';
    public const LEADERBOARD_KEY = 'platform:leaderboard';

    public function rememberActiveGames(Closure $callback, int $seconds = 30): mixed
    {
        return Cache::remember(self::ACTIVE_GAMES_KEY, $seconds, $callback);
    }

    public function rememberRecentResults(Closure $callback, int $seconds = 30): mixed
    {
        return Cache::remember(self::RECENT_RESULTS_KEY, $seconds, $callback);
    }

    public function rememberLeaderboard(Closure $callback, int $seconds = 30): mixed
    {
        return Cache::remember(self::LEADERBOARD_KEY, $seconds, $callback);
    }

    public function forgetActiveGames(): void
    {
        Cache::forget(self::ACTIVE_GAMES_KEY);
    }

    public function forgetRecentResults(): void
    {
        Cache::forget(self::RECENT_RESULTS_KEY);
    }

    public function forgetLeaderboard(): void
    {
        Cache::forget(self::LEADERBOARD_KEY);
    }

    public function forgetWalletSnapshot(int $userId): void
    {
        Cache::forget("wallet:snapshot:{$userId}");
    }

    public function forgetDashboard(int $userId): void
    {
        Cache::forget("dashboard:user:{$userId}");
    }

    public function forgetHistory(int $userId): void
    {
        Cache::forget("history:user:{$userId}");
    }

    public function forgetUserScopedCaches(int $userId): void
    {
        $this->forgetWalletSnapshot($userId);
        $this->forgetDashboard($userId);
        $this->forgetHistory($userId);
    }
}
