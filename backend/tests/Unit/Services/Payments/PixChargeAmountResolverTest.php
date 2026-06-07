<?php

namespace Tests\Unit\Services\Payments;

use App\Services\Payments\PixChargeAmountResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PixChargeAmountResolverTest extends TestCase
{
    #[Test]
    public function it_charges_a_percentage_of_the_quote_total(): void
    {
        config(['quotes.pix.charge_percentage' => 0.1]);

        $resolver = new PixChargeAmountResolver;

        $this->assertSame(0.56, $resolver->resolve(558.0));
        $this->assertSame(0.85, $resolver->resolve(852.5));
        $this->assertSame(0.06, $resolver->resolve(58.0));
    }

    #[Test]
    public function it_respects_the_configured_minimum_charge(): void
    {
        config([
            'quotes.pix.charge_percentage' => 0.1,
            'quotes.pix.minimum_charge' => 0.5,
        ]);

        $resolver = new PixChargeAmountResolver;

        $this->assertSame(0.5, $resolver->resolve(10.0));
    }
}
