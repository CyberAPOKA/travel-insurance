<?php

namespace App\ValueObjects;

final class Money
{
    public static function roundHalfUp(float $amount, int $decimals = 2): float
    {
        return round($amount, $decimals, PHP_ROUND_HALF_UP);
    }
}
