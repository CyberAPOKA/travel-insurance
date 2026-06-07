<?php

namespace Tests\Unit\Quotes\PricingRules;

use App\Services\Quotes\PricingRules\ChargedDaysCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChargedDaysCalculatorTest extends TestCase
{
    private ChargedDaysCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new ChargedDaysCalculator;
    }

    #[Test]
    public function it_calculates_trip_days_counting_start_and_end_date(): void
    {
        $startDate = Carbon::parse('2026-07-10');
        $endDate = Carbon::parse('2026-07-14');

        $this->assertSame(5, $this->calculator->tripDays($startDate, $endDate));
    }

    #[Test]
    public function it_applies_minimum_charged_days(): void
    {
        $startDate = Carbon::parse('2026-07-10');
        $endDate = Carbon::parse('2026-07-10');

        $this->assertSame(1, $this->calculator->tripDays($startDate, $endDate));
        $this->assertSame(5, $this->calculator->chargedDays($startDate, $endDate));
    }
}
