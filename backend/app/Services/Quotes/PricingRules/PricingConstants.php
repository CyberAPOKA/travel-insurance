<?php

namespace App\Services\Quotes\PricingRules;

final class PricingConstants
{
    // Dias mínimos cobrados na viagem
    public const MIN_CHARGED_DAYS = 5;

    // Taxa diária do add-on de bagagem
    public const LUGGAGE_DAILY_RATE = 3.00;

    // Taxa percentual do add-on esportes de aventura (25%)
    public const ADVENTURE_SPORTS_RATE = 0.25;

    // Quantidade mínima de viajantes para desconto de grupo
    public const GROUP_DISCOUNT_THRESHOLD = 5;

    // Percentual de desconto de grupo (10%)
    public const GROUP_DISCOUNT_RATE = 0.10;

    // Faixas etárias do multiplicador de prêmio
    public const MINOR_MAX_AGE = 17;

    public const ADULT_MIN_AGE = 18;

    public const ADULT_MAX_AGE = 64;

    public const SENIOR_MIN_AGE = 65;

    // Faixa etária para elegibilidade do add-on esportes de aventura
    public const MIN_ADVENTURE_SPORTS_AGE = self::ADULT_MIN_AGE;

    public const MAX_ADVENTURE_SPORTS_AGE = self::ADULT_MAX_AGE;

    /**
     * Exporta constantes para o calculation_breakdown da API.
     *
     * @return array<string, float|int>
     */
    public static function toArray(): array
    {
        return [
            'min_charged_days' => self::MIN_CHARGED_DAYS,
            'luggage_daily_rate' => self::LUGGAGE_DAILY_RATE,
            'adventure_sports_rate' => self::ADVENTURE_SPORTS_RATE,
            'group_discount_threshold' => self::GROUP_DISCOUNT_THRESHOLD,
            'group_discount_percentage' => (int) (self::GROUP_DISCOUNT_RATE * 100),
            'minor_max_age' => self::MINOR_MAX_AGE,
            'adult_min_age' => self::ADULT_MIN_AGE,
            'adult_max_age' => self::ADULT_MAX_AGE,
            'senior_min_age' => self::SENIOR_MIN_AGE,
            'adventure_sports_min_age' => self::MIN_ADVENTURE_SPORTS_AGE,
            'adventure_sports_max_age' => self::MAX_ADVENTURE_SPORTS_AGE,
        ];
    }
}
