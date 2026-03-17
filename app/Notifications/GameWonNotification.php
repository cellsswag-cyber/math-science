<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GameWonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $gameName,
        private readonly int $predictionNumber,
        private readonly string $amount,
        private readonly string $winningNumber,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'You won a game',
            'message' => "Your prediction {$this->predictionNumber} won in {$this->gameName}.",
            'amount' => $this->amount,
            'winning_number' => $this->winningNumber,
        ];
    }
}
