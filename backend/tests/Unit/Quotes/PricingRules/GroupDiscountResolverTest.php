<?php

namespace Tests\Unit\Quotes\PricingRules;

use App\Services\Quotes\PricingRules\GroupDiscountResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GroupDiscountResolverTest extends TestCase
{
    private GroupDiscountResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new GroupDiscountResolver;
    }

    #[Test]
    public function it_returns_zero_percent_for_less_than_five_travelers(): void
    {
        $this->assertSame(0, $this->resolver->percentageFor(1));
        $this->assertSame(0, $this->resolver->percentageFor(4));
    }

    #[Test]
    public function it_returns_ten_percent_for_five_or_more_travelers(): void
    {
        $this->assertSame(10, $this->resolver->percentageFor(5));
        $this->assertSame(10, $this->resolver->percentageFor(8));
    }

    #[Test]
    public function it_calculates_discount_amount(): void
    {
        $this->assertSame(35.0, $this->resolver->discountAmount(350.0, 10));
        $this->assertSame(0.0, $this->resolver->discountAmount(350.0, 0));
    }
}
