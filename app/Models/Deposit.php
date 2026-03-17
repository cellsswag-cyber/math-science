<?php

namespace App\Models;

use App\Domain\Wallet\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'crypto_amount',
        'paid_crypto_amount',
        'credited_amount',
        'currency',
        'price_currency',
        'pay_currency',
        'payment_id',
        'order_id',
        'wallet_address',
        'payment_url',
        'status',
        'gateway_status',
        'confirmations',
        'min_confirmations',
        'expires_at',
        'confirmed_at',
        'failed_reason',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'crypto_amount' => 'decimal:8',
            'paid_crypto_amount' => 'decimal:8',
            'credited_amount' => 'decimal:2',
            'status' => DepositStatus::class,
            'confirmations' => 'integer',
            'min_confirmations' => 'integer',
            'expires_at' => 'immutable_datetime',
            'confirmed_at' => 'immutable_datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TransactionLog::class);
    }
}
