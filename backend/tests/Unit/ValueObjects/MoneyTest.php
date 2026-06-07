<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    #[Test]
    public function it_rounds_amounts_half_up_to_two_decimals(): void
    {
        $this->assertSame(2.01, Money::roundHalfUp(2.005));
        $this->assertSame(852.5, Money::roundHalfUp(852.5));
        $this->assertSame(315.0, Money::roundHalfUp(315.0));
    }
}
