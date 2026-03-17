<?php

namespace App\Services;

use App\Domain\Entry\Enums\EntryStatus;
use App\Domain\Wallet\Enums\WithdrawStatus;
use App\Models\Entry;
use App\Models\Game;
use App\Models\WithdrawRequest;
use App\Notifications\GameWonNotification;
use App\Notifications\ResultDeclaredNotification;
use App\Notifications\WithdrawalApprovedNotification;
use App\Notifications\WithdrawalRejectedNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function sendGameWonNotifications(int $gameId): void
    {
        Entry::query()
            ->with(['user', 'game.result', 'game'])
            ->where('game_id', $gameId)
            ->where('status', EntryStatus::Win->value)
            ->get()
            ->each(function (Entry $entry): void {
                $entry->user?->notify(new GameWonNotification(
                    $entry->game?->name ?? 'Game',
                    (int) $entry->prediction_number,
                    (string) $entry->amount,
                    (string) ($entry->game?->result?->winning_number ?? ''),
                ));
            });
    }

    public function sendResultDeclaredNotifications(int $gameId): void
    {
        $game = Game::query()->with('result')->findOrFail($gameId);

        $users = Entry::query()
            ->with('user')
            ->where('game_id', $gameId)
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        Notification::send($users, new ResultDeclaredNotification(
            $game->name,
            (string) ($game->result?->winning_number ?? ''),
        ));
    }

    public function sendWithdrawalDecisionNotification(int $withdrawRequestId): void
    {
        $withdrawRequest = WithdrawRequest::query()
            ->with('user')
            ->findOrFail($withdrawRequestId);

        if (! $withdrawRequest->user) {
            return;
        }

        if ($withdrawRequest->status === WithdrawStatus::Approved) {
            $withdrawRequest->user->notify(new WithdrawalApprovedNotification(
                (string) $withdrawRequest->amount,
                $withdrawRequest->reference ?? (string) $withdrawRequest->id,
            ));

            return;
        }

        if ($withdrawRequest->status === WithdrawStatus::Rejected) {
            $withdrawRequest->user->notify(new WithdrawalRejectedNotification(
                (string) $withdrawRequest->amount,
                $withdrawRequest->reference ?? (string) $withdrawRequest->id,
            ));
        }
    }
}
