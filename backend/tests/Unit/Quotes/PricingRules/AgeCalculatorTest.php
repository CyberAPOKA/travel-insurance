<?php

namespace Tests\Unit\Quotes\PricingRules;

use App\Services\Quotes\PricingRules\AgeCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgeCalculatorTest extends TestCase
{
    private AgeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new AgeCalculator;
    }

    #[Test]
    public function it_calculates_age_at_reference_date(): void
    {
        $birthDate = Carbon::parse('1990-03-15');
        $referenceDate = Carbon::parse('2026-07-10');

        $this->assertSame(36, $this->calculator->ageAt($birthDate, $referenceDate));
    }

    #[Test]
    public function it_does_not_use_current_date(): void
    {
        $birthDate = Carbon::parse('2010-01-01');
        $tripStartDate = Carbon::parse('2026-01-02');

        $this->assertSame(16, $this->calculator->ageAt($birthDate, $tripStartDate));
    }
}
