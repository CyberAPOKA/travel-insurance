<?php

namespace App\Services\Payments\Asaas;

class AsaasPixAddressKeyService
{
    public function __construct(
        private readonly AsaasHttpClient $client,
    ) {}

    public function ensureRegistered(): void
    {
        if ($this->hasActiveKey()) {
            return;
        }

        $this->client->post('pix/addressKeys', [
            'type' => 'EVP',
        ]);
    }

    private function hasActiveKey(): bool
    {
        $response = $this->client->get('pix/addressKeys');

        $keys = $response['data'] ?? [];

        if (! is_array($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if (! is_array($key)) {
                continue;
            }

            $status = strtoupper((string) ($key['status'] ?? ''));

            if ($status === '' || $status === 'ACTIVE') {
                return true;
            }
        }

        return false;
    }
}
