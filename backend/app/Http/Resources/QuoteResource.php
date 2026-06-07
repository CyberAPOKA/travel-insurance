<?php

namespace App\Http\Resources;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quote */
class QuoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'destination' => $this->destination,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'charged_days' => $this->charged_days,
            'travelers' => QuoteTravelerResource::collection($this->whenLoaded('travelers')),
            'warnings' => $this->warnings,
            'group_discount_percentage' => $this->group_discount_percentage,
            'final_total' => (float) $this->final_total,
            'created_at' => $this->created_at?->toISOString(),
            'calculation_breakdown' => $this->calculation_breakdown,
            'payment' => $this->when(
                $this->relationLoaded('payment'),
                fn () => $this->payment
                    ? QuotePaymentResource::make($this->payment)->resolve()
                    : null,
            ),
        ];
    }
}
