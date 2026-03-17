<?php

namespace App\Models;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'locked_balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'locked_balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
