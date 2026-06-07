<?php

namespace App\Services\Payments\Asaas;

use App\Services\Payments\QuotePixPaymentService;

class AsaasWebhookProcessor
{
    public function __construct(
        private readonly QuotePixPaymentService $paymentService,
        private readonly AsaasPixGateway $pixGateway,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $event = is_string($payload['event'] ?? null) ? $payload['event'] : null;
        $payment = $payload['payment'] ?? null;

        if (! is_array($payment)) {
            return;
        }

        match ($event) {
            'PAYMENT_RECEIVED',
            'PAYMENT_CONFIRMED',
            'PAYMENT_OVERDUE',
            'PAYMENT_DELETED',
            'PAYMENT_UPDATED',
            'PAYMENT_CREATED' => $this->paymentService->syncFromAsaasPayload($payment, $event),
            default => null,
        };
    }
}
