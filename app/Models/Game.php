<?php

namespace App\Models;

use App\Domain\Game\Enums\GameStatus;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'open_time',
        'close_time',
        'result_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'open_time' => 'datetime',
            'close_time' => 'datetime',
            'result_time' => 'datetime',
            'status' => GameStatus::class,
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }
}
