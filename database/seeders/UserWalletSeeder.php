<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class UserWalletSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)
            ->create()
            ->each(function (User $user, int $index): void {
                Wallet::query()->create([
                    'user_id' => $user->id,
                    'balance' => 1000 + ($index * 100),
                    'locked_balance' => 0,
                ]);
            });
    }
}
