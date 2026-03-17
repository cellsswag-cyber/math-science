<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Services\Crypto\CryptoDepositService;
use Illuminate\Console\Command;
use Throwable;

class CheckDepositsCommand extends Command
{
    protected $signature = 'deposits:check {--payment-id= : Reconcile one payment by gateway payment ID} {--limit=100 : Number of pending deposits to check}';

    protected $description = 'Reconcile pending crypto deposits against NOWPayments and apply wallet credits.';

    public function handle(CryptoDepositService $depositService): int
    {
        $paymentId = $this->option('payment-id');

        if (is_string($paymentId) && $paymentId !== '') {
            $deposit = Deposit::query()->where('payment_id', $paymentId)->first();

            if (! $deposit instanceof Deposit) {
                $this->error(sprintf('No deposit found for payment ID [%s].', $paymentId));

                return self::FAILURE;
            }

            try {
                $updated = $depositService->syncDepositStatus($deposit);
            } catch (Throwable $exception) {
                $this->error(sprintf('Reconciliation failed: %s', $exception->getMessage()));

                return self::FAILURE;
            }

            $this->info(sprintf(
                'Deposit %s reconciled. Status=%s, confirmations=%d, credited_amount=%s',
                $updated->payment_id,
                $updated->status->value,
                $updated->confirmations,
                $updated->credited_amount ?? '0.00',
            ));

            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $stats = $depositService->reconcilePendingDeposits($limit);

        $this->info(sprintf('Processed: %d', $stats['processed']));
        $this->info(sprintf('Confirmed: %d', $stats['confirmed']));
        $this->info(sprintf('Failed/Expired: %d', $stats['failed']));
        $this->info(sprintf('Still Pending: %d', $stats['pending']));
        $this->info(sprintf('Errors: %d', $stats['errors']));

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
