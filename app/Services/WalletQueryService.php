<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Repositories\WalletRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class WalletQueryService
{
    public function __construct(
        private readonly WalletRepository $wallets,
        private readonly TransactionRepository $transactions,
    ) {}

    /**
     * @return array<string, string>
     */
    public function getWalletSnapshot(int $userId): array
    {
        return Cache::remember("wallet:snapshot:{$userId}", 10, function () use ($userId): array {
            $wallet = $this->wallets->getUserWallet($userId);

            return [
                'available_balance' => $wallet->balance,
                'locked_balance' => $wallet->locked_balance,
                'total_balance' => number_format(
                    (float) $wallet->balance + (float) $wallet->locked_balance,
                    2,
                    '.',
                    '',
                ),
            ];
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentTransactions(int $userId, int $limit = 10): array
    {
        return $this->transactions->getRecentUserTransactions($userId, $limit)
            ->map(fn (Transaction $transaction): array => $this->mapTransaction($transaction))
            ->all();
    }

    public function getTransactionHistory(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->through(fn (Transaction $transaction): array => $this->mapTransaction($transaction));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTransaction(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'type' => $transaction->type->value,
            'status' => $transaction->status->value,
            'amount' => $transaction->amount,
            'reference' => $transaction->reference,
            'meta' => $transaction->meta ?? [],
            'created_at' => optional($transaction->created_at)->toDateTimeString(),
        ];
    }
}
