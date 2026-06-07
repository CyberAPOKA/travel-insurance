<?php

namespace App\Enums;

enum DestinationZone: string
{
    case National = 'NATIONAL';
    case Americas = 'AMERICAS';
    case Europe = 'EUROPE';

    public function dailyRate(): float
    {
        return match ($this) {
            self::National => 10.00,
            self::Americas => 16.00,
            self::Europe => 22.00,
        };
    }
}
