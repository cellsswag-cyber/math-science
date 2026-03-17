<?php

namespace App\Services;

use App\Domain\Entry\Enums\EntryStatus;
use App\Domain\Game\Enums\GameStatus;
use App\Models\Entry;
use App\Models\Game;
use Carbon\Carbon;

class GameQueryService
{
    public function __construct(
        private readonly PlatformCacheService $cache,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getActiveGamesForUser(?int $userId = null): array
    {
        $games = $this->cache->rememberActiveGames(function (): array {
            return Game::query()
                ->withCount('entries')
                ->whereIn('status', GameStatus::activeValues())
                ->orderBy('open_time')
                ->get()
                ->map(fn (Game $game): array => $this->mapGame($game))
                ->all();
        });

        if ($userId === null || empty($games)) {
            return $games;
        }

        $entries = Entry::query()
            ->where('user_id', $userId)
            ->whereIn('game_id', collect($games)->pluck('id')->all())
            ->latest()
            ->get()
            ->groupBy('game_id');

        return array_map(function (array $game) use ($entries): array {
            $userEntries = $entries->get($game['id']) ?? collect();
            $latestEntry = $userEntries->first();

            return array_merge($game, [
                'has_entry' => $latestEntry !== null,
                'entry_status' => $latestEntry?->status?->value,
                'entry_label' => $this->formatEntryLabel($latestEntry?->status),
                'user_entry_count' => $userEntries->count(),
            ]);
        }, $games);
    }

    /**
     * @return array<string, mixed>
     */
    public function getGamePlayData(int $gameId, int $userId): array
    {
        $game = Game::query()
            ->with(['result'])
            ->withCount('entries')
            ->findOrFail($gameId);

        return [
            'game' => $this->mapGame($game),
            'user_entries' => Entry::query()
                ->where('user_id', $userId)
                ->where('game_id', $gameId)
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (Entry $entry): array => [
                    'id' => $entry->id,
                    'prediction_number' => $entry->prediction_number,
                    'amount' => $entry->amount,
                    'status' => $entry->status->value,
                    'created_at' => optional($entry->created_at)->toDateTimeString(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatCountdown(string $targetTime): array
    {
        $target = Carbon::parse($targetTime);
        $secondsRemaining = now()->diffInSeconds($target, false);

        return [
            'seconds_remaining' => max(0, $secondsRemaining),
            'is_expired' => $secondsRemaining <= 0,
            'label' => $secondsRemaining <= 0
                ? 'Expired'
                : now()->diffForHumans($target, [
                    'parts' => 3,
                    'short' => true,
                    'syntax' => Carbon::DIFF_ABSOLUTE,
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapGame(Game $game): array
    {
        $countdownTarget = match ($game->status) {
            GameStatus::Pending => $game->open_time,
            GameStatus::Open => $game->close_time,
            default => $game->result_time,
        };

        $countdownType = match ($game->status) {
            GameStatus::Pending => 'opens_in',
            GameStatus::Open => 'closes_in',
            default => 'result_in',
        };

        return [
            'id' => $game->id,
            'name' => $game->name,
            'status' => $game->status->value,
            'open_time' => optional($game->open_time)->toDateTimeString(),
            'close_time' => optional($game->close_time)->toDateTimeString(),
            'result_time' => optional($game->result_time)->toDateTimeString(),
            'entries_count' => $game->entries_count ?? 0,
            'countdown_target' => optional($countdownTarget)->toIso8601String(),
            'countdown_type' => $countdownType,
            'winning_number' => $game->result?->winning_number,
        ];
    }

    private function formatEntryLabel(?EntryStatus $status): ?string
    {
        return match ($status) {
            EntryStatus::Pending => 'Pending',
            EntryStatus::Win => 'Won',
            EntryStatus::Lose => 'Lost',
            EntryStatus::Refunded => 'Refunded',
            default => null,
        };
    }
}
