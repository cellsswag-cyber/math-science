<?php

namespace Tests\Feature;

use App\Domain\Entry\Enums\EntryStatus;
use App\Domain\Game\Enums\GameStatus;
use App\Domain\Wallet\Enums\TransactionType;
use App\Models\Entry;
use App\Models\Game;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_deposit_place_entry_and_receive_winnings(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        $game = Game::factory()->create([
            'status' => GameStatus::Open,
            'open_time' => now()->subHour(),
            'close_time' => now()->addHour(),
            'result_time' => now()->addHours(2),
        ]);

        $this->postJson('/test/deposit', [
            'user_id' => $user->id,
            'amount' => 100,
        ])->assertCreated();

        $this->postJson('/test/entry', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'prediction_number' => 7,
            'amount' => 25,
        ])->assertCreated();

        $game->update([
            'status' => GameStatus::Closed,
            'close_time' => now()->subMinute(),
        ]);

        $this->postJson('/test/result', [
            'game_id' => $game->id,
            'winning_number' => 7,
        ])->assertOk();

        $wallet = Wallet::query()->where('user_id', $user->id)->firstOrFail();
        $entry = Entry::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('125.00', $wallet->balance);
        $this->assertSame('0.00', $wallet->locked_balance);
        $this->assertSame(EntryStatus::Win, $entry->status);
        $this->assertDatabaseHas('results', [
            'game_id' => $game->id,
            'winning_number' => 7,
        ]);
        $this->assertCount(3, Transaction::all());
        $this->assertSame(
            [
                TransactionType::Deposit,
                TransactionType::EntryLocked,
                TransactionType::Winnings,
            ],
            Transaction::query()->orderBy('id')->get()->pluck('type')->all(),
        );
    }
}
