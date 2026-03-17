<?php

namespace App\Jobs;

use App\Models\TransactionLog;
use App\Services\Crypto\CryptoDepositService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class ProcessCryptoWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public readonly int $transactionLogId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(CryptoDepositService $depositService): void
    {
        $log = TransactionLog::query()->find($this->transactionLogId);
        if (! $log instanceof TransactionLog) {
            return;
        }

        if ($log->status === 'processed') {
            return;
        }

        $log->increment('attempts');

        if (! is_array($log->payload)) {
            throw new \RuntimeException('Webhook log payload is invalid.');
        }

        try {
            $deposit = $depositService->processPaymentUpdate($log->payload, 'webhook');

            $log->forceFill([
                'user_id' => $deposit?->user_id,
                'deposit_id' => $deposit?->id,
                'payment_id' => $deposit?->payment_id ?? $log->payment_id,
                'status' => 'processed',
                'response' => [
                    'deposit_status' => $deposit?->status->value,
                    'confirmations' => $deposit?->confirmations,
                ],
                'processed_at' => now(),
                'error_message' => null,
            ])->save();
        } catch (Throwable $exception) {
            $log->forceFill([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 2000),
                'response' => [
                    'exception' => $exception::class,
                ],
            ])->save();

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        if (! $exception instanceof Throwable) {
            return;
        }

        TransactionLog::query()
            ->whereKey($this->transactionLogId)
            ->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 2000),
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
