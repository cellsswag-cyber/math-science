<?php

namespace Database\Seeders;

use App\Domain\Game\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $schedule = [
            [
                'name' => 'Morning Momentum',
                'open_time' => now()->subHours(2),
                'close_time' => now()->addHours(2),
                'result_time' => now()->addHours(3),
                'status' => GameStatus::Open,
            ],
            [
                'name' => 'Lunch Forecast',
                'open_time' => now()->addHour(),
                'close_time' => now()->addHours(3),
                'result_time' => now()->addHours(4),
                'status' => GameStatus::Pending,
            ],
            [
                'name' => 'Evening Finale',
                'open_time' => now()->subHours(4),
                'close_time' => now()->subMinutes(30),
                'result_time' => now()->addHour(),
                'status' => GameStatus::Closed,
            ],
            [
                'name' => 'Prime Numbers',
                'open_time' => now()->subMinutes(45),
                'close_time' => now()->addMinutes(90),
                'result_time' => now()->addHours(2),
                'status' => GameStatus::Open,
            ],
            [
                'name' => 'Tomorrow Sprint',
                'open_time' => now()->addHours(12),
                'close_time' => now()->addHours(14),
                'result_time' => now()->addHours(15),
                'status' => GameStatus::Pending,
            ],
        ];

        foreach ($schedule as $game) {
            Game::query()->create($game);
        }
    }
}
