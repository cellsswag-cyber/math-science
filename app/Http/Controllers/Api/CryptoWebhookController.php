<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCryptoWebhookJob;
use App\Models\TransactionLog;
use App\Services\Crypto\NowPaymentsGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;

class CryptoWebhookController extends Controller
{
    public function __invoke(Request $request, NowPaymentsGateway $gateway): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('x-nowpayments-sig');

        if (! $gateway->verifyIpnSignature($rawPayload, $signature)) {
            TransactionLog::query()->create([
                'event_type' => 'webhook_signature_invalid',
                'source' => 'nowpayments-webhook',
                'status' => 'failed',
                'reference' => hash('sha256', $rawPayload),
                'request_signature' => $signature,
                'payload' => $this->decodePayload($rawPayload),
                'error_message' => 'Invalid NOWPayments webhook signature.',
                'processed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Invalid webhook signature.',
            ], 401);
        }

        try {
            $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return response()->json([
                'message' => 'Invalid webhook payload.',
            ], 422);
        }

        if (! is_array($payload)) {
            return response()->json([
                'message' => 'Webhook payload must be a valid JSON object.',
            ], 422);
        }

        $paymentId = isset($payload['payment_id']) ? (string) $payload['payment_id'] : null;

        $log = TransactionLog::query()->create([
            'payment_id' => $paymentId,
            'event_type' => 'webhook_received',
            'source' => 'nowpayments-webhook',
            'status' => 'queued',
            'reference' => hash('sha256', $rawPayload),
            'request_signature' => $signature,
            'payload' => $payload,
        ]);

        ProcessCryptoWebhookJob::dispatch($log->id)
            ->onQueue((string) config('crypto.queue.webhooks', 'crypto-webhooks'));

        return response()->json([
            'message' => 'Webhook accepted.',
        ], 202);
    }

    private function decodePayload(string $rawPayload): ?array
    {
        try {
            $decoded = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
