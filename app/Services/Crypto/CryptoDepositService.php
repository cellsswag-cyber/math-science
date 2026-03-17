<?php

namespace App\Services\Crypto;

use App\Domain\Wallet\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Services\WalletService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CryptoDepositService
{
    public function __construct(
        private readonly NowPaymentsGateway $gateway,
        private readonly WalletService $walletService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createDeposit(array $payload): Deposit
    {
        $userId = (int) $payload['user_id'];
        $amount = $this->normalizeFiatAmount($payload['amount']);
        $priceCurrency = strtolower((string) ($payload['price_currency'] ?? config('crypto.default_price_currency', 'usd')));
        $payCurrency = strtolower((string) ($payload['pay_currency'] ?? config('crypto.pay_currency', 'usdttrc20')));
        $orderId = (string) Str::orderedUuid();

        $gatewayPayload = [
            'price_amount' => $amount,
            'price_currency' => $priceCurrency,
            'pay_currency' => $payCurrency,
            'order_id' => $orderId,
            'order_description' => $payload['order_description'] ?? sprintf('Wallet deposit for user #%d', $userId),
            'is_fixed_rate' => true,
            'is_fee_paid_by_user' => true,
        ];

        $callbackUrl = (string) config('services.nowpayments.callback_url');
        if ($callbackUrl !== '') {
            $gatewayPayload['ipn_callback_url'] = $callbackUrl;
        }

        $gatewayResponse = $this->gateway->createPayment($gatewayPayload);

        $paymentId = isset($gatewayResponse['payment_id']) ? (string) $gatewayResponse['payment_id'] : '';
        if ($paymentId === '') {
            throw new RuntimeException('NOWPayments did not return a valid payment_id.');
        }

        $expectedCrypto = $this->normalizeCryptoAmount($gatewayResponse['pay_amount'] ?? null);
        if ($expectedCrypto === null) {
            $paymentDetails = $this->gateway->getPaymentStatus($paymentId);
            $gatewayResponse = array_replace($gatewayResponse, $paymentDetails);
            $expectedCrypto = $this->normalizeCryptoAmount($gatewayResponse['pay_amount'] ?? null);
        }

        if ($expectedCrypto === null || (float) $expectedCrypto <= 0) {
            throw new RuntimeException('Unable to determine expected USDT amount from NOWPayments response.');
        }

        return Deposit::query()->create([
            'user_id' => $userId,
            'amount' => $amount,
            'crypto_amount' => $expectedCrypto,
            'currency' => (string) config('crypto.currency', 'USDT'),
            'price_currency' => $priceCurrency,
            'pay_currency' => $payCurrency,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'wallet_address' => $gatewayResponse['pay_address'] ?? null,
            'payment_url' => $gatewayResponse['invoice_url'] ?? $gatewayResponse['payment_url'] ?? null,
            'status' => DepositStatus::Pending,
            'gateway_status' => strtolower((string) ($gatewayResponse['payment_status'] ?? 'waiting')),
            'confirmations' => 0,
            'min_confirmations' => $this->minConfirmations(),
            'expires_at' => $this->parseDate($gatewayResponse['expiration_estimate_date'] ?? null),
            'meta' => [
                'gateway' => 'nowpayments',
                'create_payment_request' => $gatewayPayload,
                'create_payment_response' => $gatewayResponse,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function processPaymentUpdate(array $payload, string $source = 'webhook'): ?Deposit
    {
        $paymentId = isset($payload['payment_id']) ? (string) $payload['payment_id'] : '';

        if ($paymentId === '') {
            throw ValidationException::withMessages([
                'payment_id' => 'payment_id is required in gateway payload.',
            ]);
        }

        return DB::transaction(function () use ($payload, $paymentId, $source): ?Deposit {
            $deposit = Deposit::query()
                ->where('payment_id', $paymentId)
                ->lockForUpdate()
                ->first();

            if (! $deposit instanceof Deposit) {
                TransactionLog::query()->create([
                    'payment_id' => $paymentId,
                    'event_type' => 'payment_unmatched',
                    'source' => $source,
                    'status' => 'failed',
                    'reference' => 'unmatched',
                    'payload' => $payload,
                    'processed_at' => now(),
                    'error_message' => 'No local deposit found for payment ID.',
                ]);

                return null;
            }

            $gatewayStatus = strtolower((string) ($payload['payment_status'] ?? $deposit->gateway_status ?? 'waiting'));
            $confirmations = max(
                (int) $deposit->confirmations,
                $this->extractConfirmations($payload, $gatewayStatus, (int) $deposit->min_confirmations),
            );

            $expectedCryptoAmount = (float) $deposit->crypto_amount;
            $actuallyPaid = (float) ($this->normalizeCryptoAmount($payload['actually_paid'] ?? null) ?? 0.0);
            if ($actuallyPaid <= 0.0 && isset($payload['pay_amount'])) {
                $actuallyPaid = (float) ($this->normalizeCryptoAmount($payload['pay_amount']) ?? 0.0);
            }

            $resolvedStatus = $this->resolveStatus(
                gatewayStatus: $gatewayStatus,
                confirmations: $confirmations,
                minConfirmations: (int) $deposit->min_confirmations,
                actuallyPaid: $actuallyPaid,
                expectedAmount: $expectedCryptoAmount,
            );

            $isAlreadyConfirmed = $deposit->status === DepositStatus::Confirmed;
            $updates = [
                'gateway_status' => $gatewayStatus,
                'confirmations' => $confirmations,
                'paid_crypto_amount' => $actuallyPaid > 0 ? $this->formatCrypto($actuallyPaid) : $deposit->paid_crypto_amount,
                'status' => $isAlreadyConfirmed ? DepositStatus::Confirmed : $resolvedStatus,
                'failed_reason' => $isAlreadyConfirmed ? null : $this->deriveFailureReason($gatewayStatus, $resolvedStatus),
            ];

            if (! $isAlreadyConfirmed && $resolvedStatus === DepositStatus::Confirmed) {
                $creditAmount = $this->resolveCreditAmount($deposit, $actuallyPaid, $expectedCryptoAmount);
                if ($creditAmount <= 0) {
                    $updates['status'] = DepositStatus::PartiallyPaid;
                    $updates['failed_reason'] = 'Received amount is too small to credit.';
                } else {
                    $creditedAmount = $this->creditWallet($deposit, $creditAmount, $payload, $source);
                    $updates['credited_amount'] = $this->formatFiat($creditedAmount);
                    $updates['confirmed_at'] = now();
                    $updates['failed_reason'] = null;
                }
            }

            $deposit->forceFill($updates)->save();

            $reference = hash('sha256', sprintf(
                '%s|%s|%s',
                $source,
                $deposit->payment_id,
                json_encode($payload, JSON_UNESCAPED_SLASHES) ?: Str::uuid()->toString(),
            ));

            TransactionLog::query()->updateOrCreate(
                [
                    'deposit_id' => $deposit->id,
                    'event_type' => 'payment_update_applied',
                    'reference' => $reference,
                ],
                [
                    'user_id' => $deposit->user_id,
                    'payment_id' => $deposit->payment_id,
                    'source' => $source,
                    'status' => $deposit->status->value,
                    'payload' => $payload,
                    'response' => [
                        'status' => $deposit->status->value,
                        'gateway_status' => $deposit->gateway_status,
                        'confirmations' => $deposit->confirmations,
                        'credited_amount' => $deposit->credited_amount,
                    ],
                    'processed_at' => now(),
                ],
            );

            return $deposit->refresh();
        });
    }

    public function syncDepositStatus(Deposit $deposit): Deposit
    {
        $payload = $this->gateway->getPaymentStatus($deposit->payment_id);
        $updated = $this->processPaymentUpdate($payload, 'reconciliation');

        return $updated instanceof Deposit ? $updated : $deposit->refresh();
    }

    /**
     * @return array{processed:int,confirmed:int,failed:int,pending:int,errors:int}
     */
    public function reconcilePendingDeposits(int $limit = 100): array
    {
        $stats = [
            'processed' => 0,
            'confirmed' => 0,
            'failed' => 0,
            'pending' => 0,
            'errors' => 0,
        ];

        $deposits = Deposit::query()
            ->whereIn('status', [
                DepositStatus::Pending->value,
                DepositStatus::PartiallyPaid->value,
            ])
            ->oldest('id')
            ->limit(max($limit, 1))
            ->get();

        /** @var Deposit $deposit */
        foreach ($deposits as $deposit) {
            try {
                $updated = $this->syncDepositStatus($deposit);
                $stats['processed']++;

                match ($updated->status) {
                    DepositStatus::Confirmed => $stats['confirmed']++,
                    DepositStatus::Failed, DepositStatus::Expired => $stats['failed']++,
                    default => $stats['pending']++,
                };
            } catch (Throwable $exception) {
                $stats['errors']++;
                TransactionLog::query()->create([
                    'user_id' => $deposit->user_id,
                    'deposit_id' => $deposit->id,
                    'payment_id' => $deposit->payment_id,
                    'event_type' => 'reconciliation_failed',
                    'source' => 'reconciliation',
                    'status' => 'failed',
                    'reference' => sprintf('reconciliation_%s', Str::uuid()->toString()),
                    'error_message' => Str::limit($exception->getMessage(), 2000),
                    'response' => [
                        'exception' => $exception::class,
                    ],
                    'processed_at' => now(),
                ]);
            }
        }

        return $stats;
    }

    public function getDepositByPaymentId(string $paymentId): ?Deposit
    {
        return Deposit::query()
            ->where('payment_id', $paymentId)
            ->first();
    }

    private function minConfirmations(): int
    {
        return max(1, (int) config('crypto.min_confirmations', 2));
    }

    private function normalizeFiatAmount(float|int|string $amount): float
    {
        $normalized = round((float) $amount, 2);
        if ($normalized <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero.',
            ]);
        }

        return $normalized;
    }

    private function normalizeCryptoAmount(mixed $amount): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $normalized = round((float) $amount, 8);
        if ($normalized < 0) {
            return null;
        }

        return $this->formatCrypto($normalized);
    }

    private function formatCrypto(float $amount): string
    {
        return number_format(round($amount, 8), 8, '.', '');
    }

    private function formatFiat(float $amount): string
    {
        return number_format(round($amount, 2), 2, '.', '');
    }

    private function extractConfirmations(array $payload, string $gatewayStatus, int $minimumRequired): int
    {
        foreach (['payin_confirmations', 'confirmations', 'network_confirmations'] as $field) {
            if (isset($payload[$field]) && is_numeric($payload[$field])) {
                return max(0, (int) $payload[$field]);
            }
        }

        if (in_array($gatewayStatus, ['finished', 'confirmed'], true)) {
            return max(1, $minimumRequired);
        }

        return 0;
    }

    private function resolveStatus(
        string $gatewayStatus,
        int $confirmations,
        int $minConfirmations,
        float $actuallyPaid,
        float $expectedAmount
    ): DepositStatus {
        if ($this->isExpired($gatewayStatus)) {
            return DepositStatus::Expired;
        }

        if ($this->isFailed($gatewayStatus)) {
            return DepositStatus::Failed;
        }

        if ($gatewayStatus === 'partially_paid') {
            return DepositStatus::PartiallyPaid;
        }

        if ($this->isConfirmedStatus($gatewayStatus) && $confirmations >= $minConfirmations) {
            if ($expectedAmount > 0 && $actuallyPaid < ($expectedAmount * $this->partialThreshold())) {
                if ($this->allowPartialPayment()) {
                    return DepositStatus::Confirmed;
                }

                return DepositStatus::PartiallyPaid;
            }

            return DepositStatus::Confirmed;
        }

        if ($actuallyPaid > 0 && $expectedAmount > 0 && $actuallyPaid < ($expectedAmount * $this->partialThreshold())) {
            return DepositStatus::PartiallyPaid;
        }

        return DepositStatus::Pending;
    }

    private function resolveCreditAmount(Deposit $deposit, float $actuallyPaid, float $expectedAmount): float
    {
        if ($expectedAmount <= 0) {
            return 0.0;
        }

        $requestedAmount = round((float) $deposit->amount, 2);
        if (! $this->allowPartialPayment() || $actuallyPaid >= ($expectedAmount * $this->partialThreshold())) {
            return $requestedAmount;
        }

        if ($actuallyPaid <= 0) {
            return 0.0;
        }

        $ratio = min(1, $actuallyPaid / $expectedAmount);

        return round($requestedAmount * $ratio, 2);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function creditWallet(Deposit $deposit, float $creditAmount, array $payload, string $source): float
    {
        $reference = $this->walletReference($deposit->payment_id);

        $existingCredit = Transaction::query()
            ->where('reference', $reference)
            ->first();

        if ($existingCredit instanceof Transaction) {
            return round((float) $existingCredit->amount, 2);
        }

        $transaction = $this->walletService->deposit(
            $deposit->user_id,
            $creditAmount,
            $reference,
            [
                'gateway' => 'nowpayments',
                'payment_id' => $deposit->payment_id,
                'order_id' => $deposit->order_id,
                'pay_currency' => $deposit->pay_currency,
                'source' => $source,
                'payload' => $payload,
            ],
        );

        TransactionLog::query()->updateOrCreate(
            [
                'deposit_id' => $deposit->id,
                'event_type' => 'wallet_credited',
                'reference' => $reference,
            ],
            [
                'user_id' => $deposit->user_id,
                'payment_id' => $deposit->payment_id,
                'source' => $source,
                'status' => 'processed',
                'response' => [
                    'wallet_transaction_id' => $transaction->id,
                    'credited_amount' => $transaction->amount,
                ],
                'processed_at' => now(),
            ],
        );

        return round((float) $transaction->amount, 2);
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function allowPartialPayment(): bool
    {
        return (bool) config('crypto.allow_partial_payment', false);
    }

    private function partialThreshold(): float
    {
        return max(0.01, min(1.0, (float) config('crypto.partial_credit_threshold', 0.99)));
    }

    private function isConfirmedStatus(string $gatewayStatus): bool
    {
        return in_array($gatewayStatus, (array) config('crypto.confirmed_statuses', ['confirmed', 'finished']), true);
    }

    private function isFailed(string $gatewayStatus): bool
    {
        $failedStatuses = (array) config('crypto.failed_statuses', ['failed', 'refunded']);

        return in_array($gatewayStatus, $failedStatuses, true) || str_starts_with($gatewayStatus, 'wrong');
    }

    private function isExpired(string $gatewayStatus): bool
    {
        return in_array($gatewayStatus, (array) config('crypto.expired_statuses', ['expired']), true);
    }

    private function deriveFailureReason(string $gatewayStatus, DepositStatus $status): ?string
    {
        return match ($status) {
            DepositStatus::Failed => sprintf('Gateway reported failed status [%s].', $gatewayStatus),
            DepositStatus::Expired => 'Payment expired before confirmation.',
            DepositStatus::PartiallyPaid => 'Received amount is lower than expected.',
            default => null,
        };
    }

    private function walletReference(string $paymentId): string
    {
        return sprintf('crypto_np_%s', $paymentId);
    }
}
