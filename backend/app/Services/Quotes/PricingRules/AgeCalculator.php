<?php

namespace App\Services\Quotes\PricingRules;

use Carbon\Carbon;

class AgeCalculator
{
    /**
     * Calcula a idade na data de referência (início da viagem, não a data atual).
     */
    public function ageAt(Carbon $birthDate, Carbon $referenceDate): int
    {
        return (int) $birthDate->diff($referenceDate)->y;
    }
}
