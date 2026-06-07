<?php

namespace App\DTO;

use App\Enums\AddOn;
use App\Enums\DestinationZone;
use Carbon\Carbon;

final readonly class QuoteRequestData
{
    /**
     * @param  list<TravelerInput>  $travelers
     */
    public function __construct(
        public DestinationZone $destination,
        public Carbon $startDate,
        public Carbon $endDate,
        public array $travelers,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            destination: DestinationZone::from($validated['destination']),
            startDate: Carbon::parse($validated['start_date'])->startOfDay(),
            endDate: Carbon::parse($validated['end_date'])->startOfDay(),
            travelers: array_map(
                static function (array $traveler): TravelerInput {
                    $addOns = array_map(
                        static fn (string $addOn) => AddOn::from($addOn),
                        $traveler['add_ons'] ?? [],
                    );

                    return new TravelerInput(
                        name: $traveler['name'],
                        birthDate: Carbon::parse($traveler['birth_date'])->startOfDay(),
                        addOns: $addOns,
                    );
                },
                $validated['travelers'],
            ),
        );
    }
}
