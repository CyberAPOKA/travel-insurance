<?php

namespace Tests\Unit\Quotes\PricingRules;

use App\Services\Quotes\PricingRules\AgeMultiplierResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgeMultiplierResolverTest extends TestCase
{
    private AgeMultiplierResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new AgeMultiplierResolver;
    }

    #[Test]
    public function it_resolves_minor_multiplier(): void
    {
        $this->assertSame(0.5, $this->resolver->resolve(16));
        $this->assertSame('0.5x (0-17)', $this->resolver->label(16, 0.5));
    }

    #[Test]
    public function it_resolves_adult_multiplier(): void
    {
        $this->assertSame(1.0, $this->resolver->resolve(36));
        $this->assertSame('1x (18-64)', $this->resolver->label(36, 1.0));
    }

    #[Test]
    public function it_resolves_senior_multiplier(): void
    {
        $this->assertSame(2.0, $this->resolver->resolve(77));
        $this->assertSame('2x (65+)', $this->resolver->label(77, 2.0));
    }
}
