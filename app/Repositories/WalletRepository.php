<?php

namespace App\Repositories;

use App\Models\Wallet;

class WalletRepository
{
    public function getUserWallet(int $userId, bool $lock = false): Wallet
    {
        Wallet::query()->firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'locked_balance' => 0]
        );

        $query = Wallet::query()->where('user_id', $userId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }

    public function updateBalance(Wallet $wallet, float $balance, ?float $lockedBalance = null): Wallet
    {
        $wallet->balance = round($balance, 2);

        if ($lockedBalance !== null) {
            $wallet->locked_balance = round($lockedBalance, 2);
        }

        $wallet->save();

        return $wallet->refresh();
    }

    public function lockBalance(Wallet $wallet, float $amount): Wallet
    {
        return $this->updateBalance(
            $wallet,
            (float) $wallet->balance - $amount,
            (float) $wallet->locked_balance + $amount,
        );
    }

    public function releaseLockedBalance(Wallet $wallet, float $amount): Wallet
    {
        return $this->updateBalance(
            $wallet,
            (float) $wallet->balance + $amount,
            (float) $wallet->locked_balance - $amount,
        );
    }

    public function consumeLockedBalance(Wallet $wallet, float $amount): Wallet
    {
        return $this->updateBalance(
            $wallet,
            (float) $wallet->balance,
            (float) $wallet->locked_balance - $amount,
        );
    }
}
