<?php

namespace App\Services\Quotes;

use App\DTO\QuoteRequestData;

final class QuoteCacheKey
{
    public static function build(QuoteRequestData $request): string
    {
        $payload = [
            'destination' => $request->destination->value,
            'start_date' => $request->startDate->toDateString(),
            'end_date' => $request->endDate->toDateString(),
            'travelers' => array_map(
                static fn ($traveler) => [
                    'name' => $traveler->name,
                    'birth_date' => $traveler->birthDate->toDateString(),
                    'add_ons' => array_map(
                        static fn ($addOn) => $addOn->value,
                        $traveler->addOns,
                    ),
                ],
                $request->travelers,
            ),
        ];

        return 'quote:'.hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
