<?php

namespace App\Services\Quotes\PricingRules;

class GroupDiscountResolver
{
    /**
     * Retorna o percentual de desconto de grupo (0 ou 10).
     */
    public function percentageFor(int $travelerCount): int
    {
        return $travelerCount >= PricingConstants::GROUP_DISCOUNT_THRESHOLD
            ? (int) (PricingConstants::GROUP_DISCOUNT_RATE * 100)
            : 0;
    }

    /**
     * Calcula o valor monetário do desconto sobre o subtotal do grupo.
     */
    public function discountAmount(float $groupTotal, int $percentage): float
    {
        return $groupTotal * ($percentage / 100);
    }
}
