<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\QuoteTraveler */
class QuoteTravelerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'birth_date' => $this->birth_date?->toDateString(),
            'add_ons' => $this->add_ons ?? [],
            'age' => $this->age,
            'subtotal' => (float) $this->subtotal,
            'applied_add_ons' => $this->applied_add_ons,
        ];
    }
}
