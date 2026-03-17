<?php

namespace App\Repositories;

use App\Domain\Wallet\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository
{
    public function createTransaction(array $attributes): Transaction
    {
        return Transaction::query()->create($attributes);
    }

    public function getRecentUserTransactions(int $userId, int $limit = 10): Collection
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function findByReference(string $reference, bool $lock = false): ?Transaction
    {
        $query = Transaction::query()->where('reference', $reference);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function updateStatus(Transaction $transaction, TransactionStatus $status): Transaction
    {
        $transaction->forceFill([
            'status' => $status,
        ])->save();

        return $transaction->refresh();
    }
}
