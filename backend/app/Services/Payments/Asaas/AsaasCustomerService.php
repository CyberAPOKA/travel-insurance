<?php

namespace App\Services\Payments\Asaas;

use App\Models\User;

class AsaasCustomerService
{
    public function __construct(
        private readonly AsaasHttpClient $client,
    ) {}

    public function resolveCustomerId(User $user): string
    {
        if ($user->asaas_customer_id) {
            return $user->asaas_customer_id;
        }

        $response = $this->client->post('customers', [
            'name' => $user->name,
            'email' => $user->email,
            'cpfCnpj' => (string) config('services.asaas.default_cpf_cnpj'),
        ]);

        $customerId = $response['id'] ?? null;

        if (! is_string($customerId) || $customerId === '') {
            throw new AsaasApiException('Asaas did not return a customer id.', 502);
        }

        $user->forceFill(['asaas_customer_id' => $customerId])->save();

        return $customerId;
    }
}
