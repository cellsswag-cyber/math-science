<?php

namespace App\Services;

use App\Domain\Wallet\Enums\TransactionStatus;
use App\Domain\Wallet\Enums\TransactionType;
use App\Domain\Wallet\Enums\WithdrawStatus;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WithdrawRequest;
use App\Repositories\TransactionRepository;
use App\Repositories\WithdrawRequestRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function __construct(
        private readonly WalletRepository $wallets,
        private readonly TransactionRepository $transactions,
        private readonly WithdrawRequestRepository $withdrawRequests,
        private readonly PlatformCacheService $cache,
    ) {}

    public function deposit(int $userId, float|string $amount, ?string $reference = null, array $meta = []): Transaction
    {
        $normalizedAmount = $this->normalizeAmount($amount);
        $reference ??= (string) Str::uuid();

        $transaction = DB::transaction(function () use ($userId, $normalizedAmount, $reference, $meta): Transaction {
            $wallet = $this->wallets->getUserWallet($userId, true);

            $this->wallets->updateBalance(
                $wallet,
                $this->toFloat($wallet->balance) + $normalizedAmount,
                $this->toFloat($wallet->locked_balance),
            );

            return $this->transactions->createTransaction([
                'user_id' => $userId,
                'type' => TransactionType::Deposit,
                'amount' => $normalizedAmount,
                'status' => TransactionStatus::Completed,
                'reference' => $reference,
                'meta' => $meta,
            ]);
        });

        $this->logTransaction('Deposit completed.', $transaction);
        $this->invalidateUserCaches($userId);

        return $transaction;
    }

    public function withdraw(int $userId, float|string $amount, ?string $reference = null, array $meta = []): WithdrawRequest
    {
        $normalizedAmount = $this->normalizeAmount($amount);
        $reference ??= (string) Str::uuid();

        $withdrawRequest = DB::transaction(function () use ($userId, $normalizedAmount, $reference, $meta): WithdrawRequest {
            $wallet = $this->wallets->getUserWallet($userId, true);

            $this->assertAvailableBalance($wallet, $normalizedAmount);
            $this->wallets->lockBalance($wallet, $normalizedAmount);

            $withdrawRequest = $this->withdrawRequests->createWithdrawRequest([
                'user_id' => $userId,
                'amount' => $normalizedAmount,
                'status' => WithdrawStatus::Pending,
                'reference' => $reference,
            ]);

            $this->transactions->createTransaction([
                'user_id' => $userId,
                'type' => TransactionType::Withdraw,
                'amount' => $normalizedAmount,
                'status' => TransactionStatus::Pending,
                'reference' => $reference,
                'meta' => array_merge($meta, [
                    'withdraw_request_id' => $withdrawRequest->id,
                ]),
            ]);

            return $withdrawRequest;
        });

        Log::channel('wallet')->info('Withdraw request created.', [
            'user_id' => $userId,
            'withdraw_request_id' => $withdrawRequest->id,
            'amount' => $withdrawRequest->amount,
            'reference' => $withdrawRequest->reference,
            'status' => $withdrawRequest->status->value,
        ]);

        $this->invalidateUserCaches($userId);

        return $withdrawRequest;
    }

    public function lockFunds(int $userId, float|string $amount, ?string $reference = null, array $meta = []): Transaction
    {
        $normalizedAmount = $this->normalizeAmount($amount);
        $reference ??= (string) Str::uuid();

        $transaction = DB::transaction(function () use ($userId, $normalizedAmount, $reference, $meta): Transaction {
            $wallet = $this->wallets->getUserWallet($userId, true);

            $this->assertAvailableBalance($wallet, $normalizedAmount);
            $this->wallets->lockBalance($wallet, $normalizedAmount);

            return $this->transactions->createTransaction([
                'user_id' => $userId,
                'type' => TransactionType::EntryLocked,
                'amount' => $normalizedAmount,
                'status' => TransactionStatus::Completed,
                'reference' => $reference,
                'meta' => $meta,
            ]);
        });

        $this->logTransaction('Entry funds locked.', $transaction);
        $this->invalidateUserCaches($userId);

        return $transaction;
    }

    public function releaseFunds(
        int $userId,
        float|string $amount,
        ?string $reference = null,
        array $meta = [],
        bool $recordTransaction = true
    ): ?Transaction {
        $normalizedAmount = $this->normalizeAmount($amount);
        $reference ??= (string) Str::uuid();

        $transaction = DB::transaction(function () use ($userId, $normalizedAmount, $reference, $meta, $recordTransaction): ?Transaction {
            $wallet = $this->wallets->getUserWallet($userId, true);

            $this->assertLockedBalance($wallet, $normalizedAmount);
            $this->wallets->releaseLockedBalance($wallet, $normalizedAmount);

            if (! $recordTransaction) {
                return null;
            }

            return $this->transactions->createTransaction([
                'user_id' => $userId,
                'type' => TransactionType::EntryRefund,
                'amount' => $normalizedAmount,
                'status' => TransactionStatus::Completed,
                'reference' => $reference,
                'meta' => $meta,
            ]);
        });

        if ($transaction instanceof Transaction) {
            $this->logTransaction('Locked funds released.', $transaction);
        }

        $this->invalidateUserCaches($userId);

        return $transaction;
    }

    public function addWinnings(int $userId, float|string $amount, ?string $reference = null, array $meta = []): Transaction
    {
        $normalizedAmount = $this->normalizeAmount($amount);
        $reference ??= (string) Str::uuid();

        $transaction = DB::transaction(function () use ($userId, $normalizedAmount, $reference, $meta): Transaction {
            $wallet = $this->wallets->getUserWallet($userId, true);

            $this->wallets->updateBalance(
                $wallet,
                $this->toFloat($wallet->balance) + $normalizedAmount,
                $this->toFloat($wallet->locked_balance),
            );

            return $this->transactions->createTransaction([
                'user_id' => $userId,
                'type' => TransactionType::Winnings,
                'amount' => $normalizedAmount,
                'status' => TransactionStatus::Completed,
                'reference' => $reference,
                'meta' => $meta,
            ]);
        });

        $this->logTransaction('Winnings credited.', $transaction);
        $this->invalidateUserCaches($userId);

        return $transaction;
    }

    public function consumeLockedFunds(int $userId, float|string $amount): Wallet
    {
        $normalizedAmount = $this->normalizeAmount($amount);

        $wallet = DB::transaction(function () use ($userId, $normalizedAmount): Wallet {
            $wallet = $this->wallets->getUserWallet($userId, true);

            $this->assertLockedBalance($wallet, $normalizedAmount);

            return $this->wallets->consumeLockedBalance($wallet, $normalizedAmount);
        });

        $this->invalidateUserCaches($userId);

        return $wallet;
    }

    public function approveWithdrawal(int $withdrawRequestId, int $approvedBy): WithdrawRequest
    {
        $withdrawRequest = DB::transaction(function () use ($withdrawRequestId, $approvedBy): WithdrawRequest {
            $withdrawRequest = $this->withdrawRequests->findById($withdrawRequestId, true);

            if ($withdrawRequest->status !== WithdrawStatus::Pending) {
                throw ValidationException::withMessages([
                    'withdraw_request_id' => 'This withdrawal request has already been processed.',
                ]);
            }

            $wallet = $this->wallets->getUserWallet($withdrawRequest->user_id, true);
            $amount = $this->normalizeAmount($withdrawRequest->amount);

            $this->assertLockedBalance($wallet, $amount);
            $this->wallets->consumeLockedBalance($wallet, $amount);

            $transaction = $this->transactions->findByReference($withdrawRequest->reference, true);

            if ($transaction instanceof Transaction) {
                $this->transactions->updateStatus($transaction, TransactionStatus::Completed);
            }

            return $this->withdrawRequests->updateStatus(
                $withdrawRequest,
                WithdrawStatus::Approved,
                $approvedBy,
            );
        });

        Log::channel('wallet')->info('Withdraw request approved.', [
            'withdraw_request_id' => $withdrawRequest->id,
            'user_id' => $withdrawRequest->user_id,
            'approved_by' => $approvedBy,
            'amount' => $withdrawRequest->amount,
        ]);

        $this->invalidateUserCaches($withdrawRequest->user_id);

        return $withdrawRequest;
    }

    public function rejectWithdrawal(int $withdrawRequestId, int $approvedBy): WithdrawRequest
    {
        $withdrawRequest = DB::transaction(function () use ($withdrawRequestId, $approvedBy): WithdrawRequest {
            $withdrawRequest = $this->withdrawRequests->findById($withdrawRequestId, true);

            if ($withdrawRequest->status !== WithdrawStatus::Pending) {
                throw ValidationException::withMessages([
                    'withdraw_request_id' => 'This withdrawal request has already been processed.',
                ]);
            }

            $wallet = $this->wallets->getUserWallet($withdrawRequest->user_id, true);
            $amount = $this->normalizeAmount($withdrawRequest->amount);

            $this->assertLockedBalance($wallet, $amount);
            $this->wallets->releaseLockedBalance($wallet, $amount);

            $transaction = $this->transactions->findByReference($withdrawRequest->reference, true);

            if ($transaction instanceof Transaction) {
                $this->transactions->updateStatus($transaction, TransactionStatus::Failed);
            }

            return $this->withdrawRequests->updateStatus(
                $withdrawRequest,
                WithdrawStatus::Rejected,
                $approvedBy,
            );
        });

        Log::channel('wallet')->info('Withdraw request rejected.', [
            'withdraw_request_id' => $withdrawRequest->id,
            'user_id' => $withdrawRequest->user_id,
            'approved_by' => $approvedBy,
            'amount' => $withdrawRequest->amount,
        ]);

        $this->invalidateUserCaches($withdrawRequest->user_id);

        return $withdrawRequest;
    }

    private function normalizeAmount(float|string $amount): float
    {
        $normalizedAmount = round((float) $amount, 2);

        if ($normalizedAmount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'The amount must be greater than zero.',
            ]);
        }

        return $normalizedAmount;
    }

    private function assertAvailableBalance(Wallet $wallet, float $amount): void
    {
        if ($this->toFloat($wallet->balance) < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient wallet balance.',
            ]);
        }
    }

    private function assertLockedBalance(Wallet $wallet, float $amount): void
    {
        if ($this->toFloat($wallet->locked_balance) < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient locked balance.',
            ]);
        }
    }

    private function toFloat(float|int|string|null $value): float
    {
        return round((float) $value, 2);
    }

    private function logTransaction(string $message, Transaction $transaction): void
    {
        Log::channel('wallet')->info($message, [
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'type' => $transaction->type->value,
            'amount' => $transaction->amount,
            'status' => $transaction->status->value,
            'reference' => $transaction->reference,
        ]);
    }

    private function invalidateUserCaches(int $userId): void
    {
        $this->cache->forgetUserScopedCaches($userId);
        $this->cache->forgetLeaderboard();
    }
}
