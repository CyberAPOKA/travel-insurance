<?php

namespace Tests\Unit\Services\Quotes;

use App\DTO\QuoteRequestData;
use App\DTO\TravelerInput;
use App\Enums\AddOn;
use App\Enums\DestinationZone;
use App\Services\Quotes\QuotePricingService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesQuotePricingService;
use Tests\TestCase;

class QuotePricingServiceTest extends TestCase
{
    use CreatesQuotePricingService;

    private QuotePricingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->makeQuotePricingService();
    }

    #[Test]
    public function it_charges_a_single_day_trip_using_the_minimum_period(): void
    {
        $request = new QuoteRequestData(
            destination: DestinationZone::National,
            startDate: Carbon::parse('2026-07-10'),
            endDate: Carbon::parse('2026-07-10'),
            travelers: [
                new TravelerInput(
                    name: 'Solo',
                    birthDate: Carbon::parse('1990-03-15'),
                ),
            ],
        );

        $result = $this->service->calculate($request);

        $this->assertSame(5, $result->chargedDays);
        $this->assertSame(50.0, $result->travelers[0]->subtotal);
    }

    #[Test]
    public function it_applies_the_senior_age_multiplier_for_travelers_65_or_older(): void
    {
        $request = new QuoteRequestData(
            destination: DestinationZone::National,
            startDate: Carbon::parse('2026-07-10'),
            endDate: Carbon::parse('2026-07-14'),
            travelers: [
                new TravelerInput(
                    name: 'Senior',
                    birthDate: Carbon::parse('1948-11-02'),
                ),
            ],
        );

        $result = $this->service->calculate($request);

        $this->assertSame(77, $result->travelers[0]->age);
        $this->assertSame(100.0, $result->travelers[0]->subtotal);
    }

    #[Test]
    public function it_uses_the_daily_rate_for_each_destination_zone(): void
    {
        $request = new QuoteRequestData(
            destination: DestinationZone::Americas,
            startDate: Carbon::parse('2026-07-10'),
            endDate: Carbon::parse('2026-07-14'),
            travelers: [
                new TravelerInput(
                    name: 'Adult',
                    birthDate: Carbon::parse('1990-03-15'),
                ),
            ],
        );

        $result = $this->service->calculate($request);

        $this->assertSame(80.0, $result->travelers[0]->subtotal);
    }

    #[Test]
    public function it_enforces_a_minimum_charged_period_of_five_days(): void
    {
        $request = new QuoteRequestData(
            destination: DestinationZone::National,
            startDate: Carbon::parse('2026-07-10'),
            endDate: Carbon::parse('2026-07-11'),
            travelers: [
                new TravelerInput(
                    name: 'Alice',
                    birthDate: Carbon::parse('1990-03-15'),
                ),
            ],
        );

        $result = $this->service->calculate($request);

        $this->assertSame(5, $result->chargedDays);
        $this->assertSame(50.0, $result->travelers[0]->subtotal);
        $this->assertSame(50.0, $result->finalTotal);
    }

    #[Test]
    public function it_calculates_age_based_on_trip_start_date_instead_of_current_date(): void
    {
        $request = new QuoteRequestData(
            destination: DestinationZone::National,
            startDate: Carbon::parse('2026-01-02'),
            endDate: Carbon::parse('2026-01-06'),
            travelers: [
                new TravelerInput(
                    name: 'Teen',
                    birthDate: Carbon::parse('2010-01-01'),
                ),
            ],
        );

        $result = $this->service->calculate($request);

        $this->assertSame(16, $result->travelers[0]->age);
        $this->assertSame(25.0, $result->travelers[0]->subtotal);
        $this->assertSame(25.0, $result->finalTotal);
    }

    #[Test]
    public function it_denies_adventure_sports_for_non_eligible_travelers_and_returns_warning(): void
    {
        $request = new QuoteRequestData(
            destination: DestinationZone::National,
            startDate: Carbon::parse('2026-07-10'),
            endDate: Carbon::parse('2026-07-14'),
            travelers: [
                new TravelerInput(
                    name: 'John',
                    birthDate: Carbon::parse('1948-11-02'),
                    addOns: [AddOn::AdventureSports, AddOn::Luggage],
                ),
            ],
        );

        $result = $this->service->calculate($request);

        $this->assertSame(
            [[
                'code' => 'adventure_sports_age_out_of_range',
                'params' => [
                    'travelerName' => 'John',
                    'minAge' => 18,
                    'maxAge' => 64,
                ],
            ]],
            $result->warnings,
        );
        $this->assertSame(['LUGGAGE'], $result->travelers[0]->appliedAddOns);
        $this->assertSame(115.0, $result->travelers[0]->subtotal);
        $this->assertSame(115.0, $result->finalTotal);
    }

    #[Test]
    public function it_applies_a_ten_percent_group_discount_for_five_or_more_travelers(): void
    {
        $travelers = array_map(
            static fn (int $index) => new TravelerInput(
                name: "Traveler {$index}",
                birthDate: Carbon::parse('1990-03-15'),
            ),
            range(1, 5),
        );

        $request = new QuoteRequestData(
            destination: DestinationZone::National,
            startDate: Carbon::parse('2026-07-10'),
            endDate: Carbon::parse('2026-07-16'),
            travelers: $travelers,
        );

        $result = $this->service->calculate($request);

        $this->assertSame(10, $result->groupDiscountPercentage);
        $this->assertSame(315.0, $result->finalTotal);
    }

    #[Test]
    public function it_calculates_a_complete_quote_with_multiple_travelers_and_add_ons(): void
    {
        $request = new QuoteRequestData(
            destination: DestinationZone::Europe,
            startDate: Carbon::parse('2026-07-10'),
            endDate: Carbon::parse('2026-07-20'),
            travelers: [
                new TravelerInput(
                    name: 'Ana',
                    birthDate: Carbon::parse('1990-03-15'),
                    addOns: [AddOn::Luggage, AddOn::AdventureSports],
                ),
                new TravelerInput(
                    name: 'John',
                    birthDate: Carbon::parse('1948-11-02'),
                    addOns: [AddOn::AdventureSports, AddOn::Luggage],
                ),
            ],
        );

        $result = $this->service->calculate($request);

        $this->assertSame(11, $result->chargedDays);
        $this->assertSame('Ana', $result->travelers[0]->name);
        $this->assertSame(36, $result->travelers[0]->age);
        $this->assertSame(335.5, $result->travelers[0]->subtotal);
        $this->assertSame(['ADVENTURE_SPORTS', 'LUGGAGE'], $result->travelers[0]->appliedAddOns);
        $this->assertSame('John', $result->travelers[1]->name);
        $this->assertSame(77, $result->travelers[1]->age);
        $this->assertSame(517.0, $result->travelers[1]->subtotal);
        $this->assertSame(['LUGGAGE'], $result->travelers[1]->appliedAddOns);
        $this->assertSame(
            [[
                'code' => 'adventure_sports_age_out_of_range',
                'params' => [
                    'travelerName' => 'John',
                    'minAge' => 18,
                    'maxAge' => 64,
                ],
            ]],
            $result->warnings,
        );
        $this->assertSame(0, $result->groupDiscountPercentage);
        $this->assertSame(852.5, $result->finalTotal);
    }
}
