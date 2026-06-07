<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Quote;
use App\Models\QuotePayment;
use App\Models\User;
use App\Services\Payments\Asaas\AsaasCustomerService;
use App\Services\Payments\Asaas\AsaasPixAddressKeyService;
use App\Services\Payments\Asaas\AsaasPixGateway;
use App\Services\Payments\Asaas\AsaasPixQrCodeData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class QuotePixPaymentService
{
    public function __construct(
        private readonly AsaasCustomerService $customerService,
        private readonly AsaasPixGateway $pixGateway,
        private readonly AsaasPixAddressKeyService $pixAddressKeyService,
        private readonly PixChargeAmountResolver $chargeAmountResolver,
    ) {}

    public function findForQuote(Quote $quote): ?QuotePayment
    {
        $payment = $quote->payment;

        if ($payment === null) {
            return null;
        }

        $payment = $this->normalizePixExpiration($payment);

        return $this->syncExpirationStatus($payment);
    }

    public function createPixPayment(Quote $quote, User $user): QuotePayment
    {
        $existing = $quote->payment;

        if ($existing !== null) {
            if ($existing->status->isPaid()) {
                throw new ConflictHttpException('This quote has already been paid.');
            }

            return $this->refreshPixData($existing);
        }

        return DB::transaction(function () use ($quote, $user) {
            $this->pixAddressKeyService->ensureRegistered();

            $customerId = $this->customerService->resolveCustomerId($user);
            $chargeAmount = $this->chargeAmountResolver->resolve((float) $quote->final_total);
            $asaasPayment = $this->pixGateway->createPixPayment($customerId, $quote, $chargeAmount);
            $pixQrCode = AsaasPixQrCodeData::fromApiResponse(
                $this->pixGateway->fetchPixQrCode((string) $asaasPayment['id']),
            );

            return QuotePayment::query()->create([
                'quote_id' => $quote->id,
                'user_id' => $user->id,
                'asaas_payment_id' => (string) $asaasPayment['id'],
                'status' => PaymentStatus::fromAsaasStatus((string) ($asaasPayment['status'] ?? 'PENDING')),
                'value' => $chargeAmount,
                'due_date' => (string) ($asaasPayment['dueDate'] ?? Carbon::today()->toDateString()),
                'pix_encoded_image' => $pixQrCode['encodedImage'],
                'pix_payload' => $pixQrCode['payload'],
                'pix_expiration_date' => $this->pixExpiresAt(),
            ]);
        });
    }

    public function refreshPixData(QuotePayment $payment): QuotePayment
    {
        if ($payment->status->isPaid()) {
            return $payment;
        }

        $pixQrCode = AsaasPixQrCodeData::fromApiResponse(
            $this->pixGateway->fetchPixQrCode($payment->asaas_payment_id),
        );

        $payment->update([
            'status' => PaymentStatus::Pending,
            'pix_encoded_image' => $pixQrCode['encodedImage'] ?? $payment->pix_encoded_image,
            'pix_payload' => $pixQrCode['payload'] ?? $payment->pix_payload,
            'pix_expiration_date' => $this->pixExpiresAt(),
        ]);

        return $payment->fresh();
    }

    /**
     * @param  array<string, mixed>  $asaasPayment
     */
    public function syncFromAsaasPayload(array $asaasPayment, ?string $event = null): ?QuotePayment
    {
        $asaasPaymentId = $asaasPayment['id'] ?? null;

        if (! is_string($asaasPaymentId) || $asaasPaymentId === '') {
            return null;
        }

        $payment = QuotePayment::query()
            ->where('asaas_payment_id', $asaasPaymentId)
            ->first();

        if ($payment === null) {
            $quoteId = $this->pixGateway->quoteIdFromExternalReference(
                is_string($asaasPayment['externalReference'] ?? null)
                    ? $asaasPayment['externalReference']
                    : null,
            );

            if ($quoteId === null) {
                return null;
            }

            $payment = QuotePayment::query()->where('quote_id', $quoteId)->first();
        }

        if ($payment === null) {
            return null;
        }

        $status = PaymentStatus::fromAsaasStatus((string) ($asaasPayment['status'] ?? 'PENDING'));

        if ($event === 'PAYMENT_RECEIVED' || $event === 'PAYMENT_CONFIRMED') {
            $status = PaymentStatus::Paid;
        }

        $attributes = [
            'status' => $status,
            'value' => (float) ($asaasPayment['value'] ?? $payment->value),
        ];

        if ($status->isPaid() && $payment->paid_at === null) {
            $paymentDate = $asaasPayment['paymentDate'] ?? $asaasPayment['clientPaymentDate'] ?? null;
            $attributes['paid_at'] = $paymentDate
                ? Carbon::parse((string) $paymentDate)
                : now();
        }

        $payment->update($attributes);

        return $payment->fresh();
    }

    private function normalizePixExpiration(QuotePayment $payment): QuotePayment
    {
        if ($payment->status->isPaid() || $payment->pix_expiration_date === null) {
            return $payment;
        }

        $maxAllowed = now()->addMinutes((int) config('quotes.pix.expiration_minutes', 60));

        if ($payment->pix_expiration_date->gt($maxAllowed)) {
            $payment->update(['pix_expiration_date' => $maxAllowed]);

            return $payment->fresh();
        }

        return $payment;
    }

    private function syncExpirationStatus(QuotePayment $payment): QuotePayment
    {
        if (
            $payment->status === PaymentStatus::Pending
            && $payment->pix_expiration_date?->isPast()
        ) {
            $payment->update(['status' => PaymentStatus::Overdue]);

            return $payment->fresh();
        }

        return $payment;
    }

    private function pixExpiresAt(): Carbon
    {
        return now()->addMinutes((int) config('quotes.pix.expiration_minutes', 60));
    }
}
