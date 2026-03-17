<?php

return [
    'gateway' => env('CRYPTO_GATEWAY', 'nowpayments'),
    'currency' => env('CRYPTO_CURRENCY', 'USDT'),
    'pay_currency' => env('CRYPTO_USDT_NETWORK', 'usdttrc20'),
    'default_price_currency' => env('CRYPTO_DEFAULT_PRICE_CURRENCY', 'usd'),
    'min_confirmations' => max(1, (int) env('CRYPTO_MIN_CONFIRMATIONS', 2)),
    'deposit_rate_limit_per_minute' => max(1, (int) env('CRYPTO_DEPOSIT_RATE_LIMIT', 5)),
    'allow_partial_payment' => filter_var(env('CRYPTO_ALLOW_PARTIAL_PAYMENT', false), FILTER_VALIDATE_BOOL),
    'partial_credit_threshold' => (float) env('CRYPTO_PARTIAL_CREDIT_THRESHOLD', 0.99),
    'confirmed_statuses' => ['confirmed', 'finished'],
    'failed_statuses' => ['failed', 'refunded'],
    'expired_statuses' => ['expired'],
    'queue' => [
        'webhooks' => env('CRYPTO_WEBHOOK_QUEUE', 'crypto-webhooks'),
    ],
];
