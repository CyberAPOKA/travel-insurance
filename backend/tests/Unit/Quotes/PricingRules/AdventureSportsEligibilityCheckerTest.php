<?php

namespace Tests\Unit\Quotes\PricingRules;

use App\Services\Quotes\PricingRules\AdventureSportsEligibilityChecker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdventureSportsEligibilityCheckerTest extends TestCase
{
    private AdventureSportsEligibilityChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checker = new AdventureSportsEligibilityChecker;
    }

    #[Test]
    public function it_allows_adults(): void
    {
        $this->assertTrue($this->checker->isEligible(18));
        $this->assertTrue($this->checker->isEligible(36));
        $this->assertTrue($this->checker->isEligible(64));
    }

    #[Test]
    public function it_denies_minors(): void
    {
        $this->assertFalse($this->checker->isEligible(17));
    }

    #[Test]
    public function it_denies_seniors(): void
    {
        $this->assertFalse($this->checker->isEligible(65));
        $this->assertFalse($this->checker->isEligible(77));
    }
}
