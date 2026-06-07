<?php

namespace App\Services\Quotes\PricingRules;

class AgeMultiplierResolver
{
    /**
     * Retorna o multiplicador de prêmio conforme a faixa etária.
     */
    public function resolve(int $age): float
    {
        return match (true) {
            $age <= PricingConstants::MINOR_MAX_AGE => 0.5,
            $age >= PricingConstants::ADULT_MIN_AGE && $age <= PricingConstants::ADULT_MAX_AGE => 1.0,
            $age >= PricingConstants::SENIOR_MIN_AGE => 2.0,
        };
    }

    /**
     * Gera rótulo legível para exibição no breakdown (ex.: "1x (18-64)").
     */
    public function label(int $age, float $multiplier): string
    {
        if ($age <= PricingConstants::MINOR_MAX_AGE) {
            return sprintf('%sx (0-%d)', $multiplier, PricingConstants::MINOR_MAX_AGE);
        }

        if ($age >= PricingConstants::ADULT_MIN_AGE && $age <= PricingConstants::ADULT_MAX_AGE) {
            return sprintf(
                '%sx (%d-%d)',
                $multiplier,
                PricingConstants::ADULT_MIN_AGE,
                PricingConstants::ADULT_MAX_AGE,
            );
        }

        return sprintf('%sx (%d+)', $multiplier, PricingConstants::SENIOR_MIN_AGE);
    }
}
