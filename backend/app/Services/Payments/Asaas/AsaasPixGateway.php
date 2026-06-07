<?php

namespace App\Services\Payments\Asaas;

use App\Models\Quote;
use Carbon\Carbon;

class AsaasPixGateway
{
    public function __construct(
        private readonly AsaasHttpClient $client,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function createPixPayment(string $customerId, Quote $quote, float $chargeAmount): array
    {
        return $this->client->post('payments', [
            'customer' => $customerId,
            'billingType' => 'PIX',
            'value' => $chargeAmount,
            'dueDate' => Carbon::today()->toDateString(),
            'description' => sprintf('Travel insurance quote #%d', $quote->id),
            'externalReference' => $this->externalReference($quote),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchPixQrCode(string $asaasPaymentId): array
    {
        return $this->client->get("payments/{$asaasPaymentId}/pixQrCode");
    }

    public function externalReference(Quote $quote): string
    {
        return 'quote:'.$quote->id;
    }

    public function quoteIdFromExternalReference(?string $externalReference): ?int
    {
        if ($externalReference === null || ! str_starts_with($externalReference, 'quote:')) {
            return null;
        }

        $quoteId = (int) substr($externalReference, strlen('quote:'));

        return $quoteId > 0 ? $quoteId : null;
    }
}
