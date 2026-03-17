<?php

namespace App\Services;

use App\Domain\Entry\Enums\EntryStatus;
use App\Models\Entry;
use App\Models\Result;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        private readonly WalletQueryService $wallets,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(int $userId): array
    {
        return Cache::remember("dashboard:user:{$userId}", 15, function () use ($userId): array {
            $user = User::query()->findOrFail($userId);

            $recentEntries = Entry::query()
                ->with(['game', 'game.result'])
                ->where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Entry $entry): array => [
                    'id' => $entry->id,
                    'game_name' => $entry->game?->name,
                    'prediction_number' => $entry->prediction_number,
                    'amount' => $entry->amount,
                    'status' => $entry->status->value,
                    'winning_number' => $entry->game?->result?->winning_number,
                    'created_at' => optional($entry->created_at)->toDateTimeString(),
                ])
                ->all();

            $recentResults = Result::query()
                ->with('game')
                ->whereHas('game.entries', fn ($query) => $query->where('user_id', $userId))
                ->latest('declared_at')
                ->limit(5)
                ->get()
                ->map(fn (Result $result): array => [
                    'id' => $result->id,
                    'game_name' => $result->game?->name,
                    'winning_number' => $result->winning_number,
                    'declared_at' => optional($result->declared_at)->toDateTimeString(),
                ])
                ->all();

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'wallet' => $this->wallets->getWalletSnapshot($userId),
                'recent_entries' => $recentEntries,
                'recent_results' => $recentResults,
                'stats' => [
                    'total_entries' => Entry::query()->where('user_id', $userId)->count(),
                    'wins' => Entry::query()->where('user_id', $userId)->where('status', EntryStatus::Win->value)->count(),
                    'losses' => Entry::query()->where('user_id', $userId)->where('status', EntryStatus::Lose->value)->count(),
                ],
                'notifications' => $user->unreadNotifications
                    ->take(5)
                    ->map(fn ($notification): array => [
                        'id' => $notification->id,
                        'type' => class_basename($notification->type),
                        'data' => $notification->data,
                        'created_at' => optional($notification->created_at)->toDateTimeString(),
                    ])
                    ->all(),
            ];
        });
    }
}
