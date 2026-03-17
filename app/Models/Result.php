<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'winning_number',
        'declared_at',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'winning_number' => 'integer',
            'declared_at' => 'datetime',
            'settled_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
