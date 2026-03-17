<?php

namespace App\Services\Crypto;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use JsonException;
use RuntimeException;

class NowPaymentsGateway
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createPayment(array $payload): array
    {
        return $this->request('POST', '/payment', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaymentStatus(string $paymentId): array
    {
        return $this->request('GET', sprintf('/payment/%s', $paymentId));
    }

    public function verifyIpnSignature(string $rawPayload, ?string $signature): bool
    {
        $secret = (string) config('services.nowpayments.ipn_secret');

        if ($secret === '' || $signature === null || trim($signature) === '') {
            return false;
        }

        try {
            $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        if (! is_array($payload)) {
            return false;
        }

        $sortedPayload = $this->sortRecursively($payload);
        $serialized = json_encode($sortedPayload, JSON_UNESCAPED_SLASHES);

        if (! is_string($serialized)) {
            return false;
        }

        $calculatedSignature = hash_hmac('sha512', $serialized, $secret);

        return hash_equals(strtolower($calculatedSignature), strtolower(trim($signature)));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function request(string $method, string $uri, array $payload = []): array
    {
        $response = match (strtoupper($method)) {
            'POST' => $this->client()->post($uri, $payload),
            'GET' => $this->client()->get($uri, $payload),
            default => throw new RuntimeException(sprintf('Unsupported NOWPayments HTTP method [%s].', $method)),
        };

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'NOWPayments request failed with status %d: %s',
                $response->status(),
                $response->body(),
            ));
        }

        $decoded = $response->json();

        if (! is_array($decoded)) {
            throw new RuntimeException('NOWPayments response could not be decoded as JSON object.');
        }

        return $decoded;
    }

    private function client(): PendingRequest
    {
        $apiKey = (string) config('services.nowpayments.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('NOWPAYMENTS_API_KEY is not configured.');
        }

        return $this->http->baseUrl((string) config('services.nowpayments.base_url', 'https://api.nowpayments.io/v1'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.nowpayments.timeout', 15))
            ->retry(2, 200)
            ->withHeaders([
                'x-api-key' => $apiKey,
            ]);
    }

    /**
     * @param  array<int|string, mixed>  $payload
     * @return array<int|string, mixed>
     */
    private function sortRecursively(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->sortRecursively($value);
            }
        }

        if ($this->isAssociative($payload)) {
            ksort($payload);
        }

        return $payload;
    }

    /**
     * @param  array<int|string, mixed>  $array
     */
    private function isAssociative(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
