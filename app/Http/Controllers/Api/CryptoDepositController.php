<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crypto\CreateDepositRequest;
use App\Models\Deposit;
use App\Services\Crypto\CryptoDepositService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CryptoDepositController extends Controller
{
    public function store(CreateDepositRequest $request, CryptoDepositService $depositService): JsonResponse
    {
        $deposit = $depositService->createDeposit($request->validated());

        return response()->json([
            'message' => 'Deposit order created successfully.',
            'data' => $this->formatDeposit($deposit),
        ], 201);
    }

    public function show(string $paymentId): JsonResponse
    {
        $deposit = Deposit::query()
            ->where('payment_id', $paymentId)
            ->first();

        if (! $deposit instanceof Deposit) {
            throw new NotFoundHttpException('Deposit not found for the given payment ID.');
        }

        return response()->json([
            'message' => 'Deposit status fetched successfully.',
            'data' => $this->formatDeposit($deposit),
        ]);
    }

    private function formatDeposit(Deposit $deposit): array
    {
        $cryptoAmount = (string) $deposit->crypto_amount;
        $paymentUri = $deposit->wallet_address !== null
            ? sprintf('tron:%s?amount=%s', $deposit->wallet_address, $cryptoAmount)
            : null;

        return [
            'id' => $deposit->id,
            'user_id' => $deposit->user_id,
            'payment_id' => $deposit->payment_id,
            'status' => $deposit->status->value,
            'gateway_status' => $deposit->gateway_status,
            'amount' => (string) $deposit->amount,
            'credited_amount' => $deposit->credited_amount !== null ? (string) $deposit->credited_amount : null,
            'expected_amount' => $cryptoAmount,
            'paid_crypto_amount' => $deposit->paid_crypto_amount !== null ? (string) $deposit->paid_crypto_amount : null,
            'currency' => $deposit->currency,
            'price_currency' => strtoupper($deposit->price_currency),
            'pay_currency' => $deposit->pay_currency,
            'wallet_address' => $deposit->wallet_address,
            'payment_url' => $deposit->payment_url,
            'payment_uri' => $paymentUri,
            'confirmations' => $deposit->confirmations,
            'min_confirmations' => $deposit->min_confirmations,
            'expires_at' => $deposit->expires_at?->toIso8601String(),
            'confirmed_at' => $deposit->confirmed_at?->toIso8601String(),
            'created_at' => $deposit->created_at?->toIso8601String(),
        ];
    }
}
