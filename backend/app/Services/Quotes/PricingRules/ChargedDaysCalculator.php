<?php

namespace App\Services\Quotes\PricingRules;

use Carbon\Carbon;

class ChargedDaysCalculator
{
    /**
     * Conta os dias da viagem incluindo início e fim.
     */
    public function tripDays(Carbon $startDate, Carbon $endDate): int
    {
        return (int) $startDate->diffInDays($endDate) + 1;
    }

    /**
     * Aplica o mínimo de dias cobrados sobre a duração real da viagem.
     */
    public function chargedDays(Carbon $startDate, Carbon $endDate): int
    {
        return max(PricingConstants::MIN_CHARGED_DAYS, $this->tripDays($startDate, $endDate));
    }
}
