<?php

namespace Tests\Unit\DTO;

use App\DTO\QuoteRequestData;
use App\Enums\AddOn;
use App\Enums\DestinationZone;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuoteRequestDataTest extends TestCase
{
    #[Test]
    public function it_builds_a_quote_request_from_validated_input(): void
    {
        $request = QuoteRequestData::fromArray([
            'destination' => 'EUROPE',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-20',
            'travelers' => [
                [
                    'name' => 'Ana',
                    'birth_date' => '1990-03-15',
                    'add_ons' => ['LUGGAGE', 'ADVENTURE_SPORTS'],
                ],
            ],
        ]);

        $this->assertSame(DestinationZone::Europe, $request->destination);
        $this->assertSame('2026-07-10', $request->startDate->toDateString());
        $this->assertSame('2026-07-20', $request->endDate->toDateString());
        $this->assertCount(1, $request->travelers);
        $this->assertSame('Ana', $request->travelers[0]->name);
        $this->assertSame([AddOn::Luggage, AddOn::AdventureSports], $request->travelers[0]->addOns);
    }
}
