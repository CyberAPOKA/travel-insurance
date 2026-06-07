<?php

namespace App\Http\Resources;

use App\Models\QuotePayment;
use App\Services\Payments\Asaas\AsaasEnvironment;
use App\Services\Payments\Asaas\AsaasPixQrCodeData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuotePayment */
class QuotePaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing('quote');

        return [
            'id' => $this->id,
            'quote_id' => $this->quote_id,
            'status' => $this->status->value,
            'value' => (float) $this->value,
            'quote_total' => (float) ($this->quote?->final_total ?? 0),
            'charge_percentage' => (float) config('quotes.pix.charge_percentage', 0.1),
            'due_date' => $this->due_date->toDateString(),
            'pix_encoded_image' => AsaasPixQrCodeData::sanitizeEncodedImage($this->pix_encoded_image),
            'pix_payload' => $this->pix_payload,
            'pix_expiration_date' => $this->pix_expiration_date?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'environment' => AsaasEnvironment::isSandbox() ? 'sandbox' : 'production',
        ];
    }
}
