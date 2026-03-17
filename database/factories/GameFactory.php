<?php

namespace Database\Factories;

use App\Domain\Game\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openTime = now()->addHours($this->faker->numberBetween(-3, 6));
        $closeTime = (clone $openTime)->addHour();
        $resultTime = (clone $closeTime)->addMinutes(30);

        return [
            'name' => $this->faker->unique()->words(2, true),
            'open_time' => $openTime,
            'close_time' => $closeTime,
            'result_time' => $resultTime,
            'status' => GameStatus::Pending,
        ];
    }
}
