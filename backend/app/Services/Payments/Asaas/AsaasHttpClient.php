<?php

namespace App\Services\Payments\Asaas;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AsaasHttpClient
{
    public function get(string $path): array
    {
        $this->ensureConfigured();

        $response = $this->client()->get($this->url($path));

        if (! $response->successful()) {
            throw AsaasApiException::fromResponse($response);
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function post(string $path, array $payload): array
    {
        $this->ensureConfigured();

        $response = $this->client()->post($this->url($path), $payload);

        if (! $response->successful()) {
            throw AsaasApiException::fromResponse($response);
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    private function ensureConfigured(): void
    {
        if (blank(config('services.asaas.api_key'))) {
            throw new AsaasApiException(
                'PIX payment provider is not configured.',
                503,
            );
        }
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'access_token' => (string) config('services.asaas.api_key'),
            'Content-Type' => 'application/json',
            'User-Agent' => config('app.name', 'travel-insurance').'/1.0',
        ])->acceptJson();
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.asaas.base_url'), '/').'/'.ltrim($path, '/');
    }
}
