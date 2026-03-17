<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WithdrawalApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $amount,
        private readonly string $reference,
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
            'title' => 'Withdrawal approved',
            'message' => "Your withdrawal request {$this->reference} was approved.",
            'amount' => $this->amount,
        ];
    }
}
