<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ResultDeclaredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $gameName,
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
            'title' => 'Result declared',
            'message' => "The result for {$this->gameName} has been declared.",
            'winning_number' => $this->winningNumber,
        ];
    }
}
