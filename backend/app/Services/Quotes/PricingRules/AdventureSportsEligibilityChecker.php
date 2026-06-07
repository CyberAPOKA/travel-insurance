<?php

namespace App\Services\Quotes\PricingRules;

class AdventureSportsEligibilityChecker
{
    /**
     * Verifica se a idade permite o add-on ADVENTURE_SPORTS.
     */
    public function isEligible(int $age): bool
    {
        return $age >= PricingConstants::MIN_ADVENTURE_SPORTS_AGE
            && $age <= PricingConstants::MAX_ADVENTURE_SPORTS_AGE;
    }
}
