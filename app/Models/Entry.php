<?php

namespace App\Models;

use App\Domain\Entry\Enums\EntryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'prediction_number',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'prediction_number' => 'integer',
            'amount' => 'decimal:2',
            'status' => EntryStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
