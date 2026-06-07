<?php

namespace App\Services\Payments;

final class PixChargeAmountResolver
{
    public function resolve(float $quoteTotal): float
    {
        $percentage = (float) config('quotes.pix.charge_percentage', 0.1);
        $minimum = (float) config('quotes.pix.minimum_charge', 0.01);

        $amount = round($quoteTotal * ($percentage / 100), 2, PHP_ROUND_HALF_UP);

        return max($minimum, $amount);
    }
}
