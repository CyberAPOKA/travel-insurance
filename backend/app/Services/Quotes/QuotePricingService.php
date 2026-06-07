<?php

namespace App\Services\Quotes;

use App\DTO\QuoteRequestData;
use App\DTO\QuoteResult;
use App\DTO\TravelerInput;
use App\DTO\TravelerQuoteResult;
use App\Enums\AddOn;
use App\Services\Quotes\PricingRules\AdventureSportsEligibilityChecker;
use App\Services\Quotes\PricingRules\AgeCalculator;
use App\Services\Quotes\PricingRules\AgeMultiplierResolver;
use App\Services\Quotes\PricingRules\ChargedDaysCalculator;
use App\Services\Quotes\PricingRules\GroupDiscountResolver;
use App\Services\Quotes\PricingRules\PricingConstants;
use App\Services\Quotes\Support\AddOnChecker;
use App\ValueObjects\Money;
use Carbon\Carbon;

class QuotePricingService
{
    public function __construct(
        private readonly ChargedDaysCalculator $chargedDaysCalculator,
        private readonly AgeCalculator $ageCalculator,
        private readonly AgeMultiplierResolver $ageMultiplierResolver,
        private readonly AdventureSportsEligibilityChecker $adventureSportsEligibilityChecker,
        private readonly GroupDiscountResolver $groupDiscountResolver,
        private readonly AddOnChecker $addOnChecker,
    ) {}

    /**
     * Orquestra o cálculo completo da cotação e monta o breakdown para a API.
     */
    public function calculate(QuoteRequestData $request): QuoteResult
    {
        $tripDays = $this->chargedDaysCalculator->tripDays($request->startDate, $request->endDate);
        $chargedDays = $this->chargedDaysCalculator->chargedDays($request->startDate, $request->endDate);
        $dailyRate = $request->destination->dailyRate();

        $warnings = [];
        $travelerResults = [];
        $travelerBreakdowns = [];

        foreach ($request->travelers as $traveler) {
            [$travelerResult, $travelerBreakdown] = $this->calculateTravelerQuote(
                traveler: $traveler,
                startDate: $request->startDate,
                dailyRate: $dailyRate,
                chargedDays: $chargedDays,
                warnings: $warnings,
            );

            $travelerResults[] = $travelerResult;
            $travelerBreakdowns[] = $travelerBreakdown;
        }

        $groupTotal = array_sum(
            array_map(
                static fn (TravelerQuoteResult $result): float => $result->rawSubtotal,
                $travelerResults,
            ),
        );

        $groupDiscountPercentage = $this->groupDiscountResolver->percentageFor(count($request->travelers));
        $discountAmount = $this->groupDiscountResolver->discountAmount($groupTotal, $groupDiscountPercentage);
        $finalTotal = Money::roundHalfUp($groupTotal - $discountAmount);

        return new QuoteResult(
            chargedDays: $chargedDays,
            travelers: $travelerResults,
            warnings: $warnings,
            groupDiscountPercentage: $groupDiscountPercentage,
            finalTotal: $finalTotal,
            calculationBreakdown: [
                'constants' => PricingConstants::toArray(),
                'trip' => [
                    'destination' => $request->destination->value,
                    'daily_rate' => $dailyRate,
                    'start_date' => $request->startDate->toDateString(),
                    'end_date' => $request->endDate->toDateString(),
                    'trip_days' => $tripDays,
                    'charged_days' => $chargedDays,
                    'min_charged_days' => PricingConstants::MIN_CHARGED_DAYS,
                    'min_charged_days_applied' => $tripDays < PricingConstants::MIN_CHARGED_DAYS,
                    'charged_days_formula' => 'max(min_charged_days, trip_days)',
                ],
                'travelers' => $travelerBreakdowns,
                'summary' => [
                    'travelers_count' => count($request->travelers),
                    'group_subtotal_before_discount' => $groupTotal,
                    'group_discount_threshold' => PricingConstants::GROUP_DISCOUNT_THRESHOLD,
                    'group_discount_percentage' => $groupDiscountPercentage,
                    'group_discount_amount' => $discountAmount,
                    'final_total' => $finalTotal,
                    'rounding' => 'half_up',
                ],
            ],
        );
    }

    /**
     * @param  list<array{code: string, params: array<string, int|string>}>  $warnings
     * @return array{0: TravelerQuoteResult, 1: array<string, mixed>}
     */
    private function calculateTravelerQuote(
        TravelerInput $traveler,
        Carbon $startDate,
        float $dailyRate,
        int $chargedDays,
        array &$warnings,
    ): array {
        // Idade na data de início da viagem — define multiplicador etário e elegibilidade de add-ons.
        $age = $this->ageCalculator->ageAt($traveler->birthDate, $startDate);
        $ageMultiplier = $this->ageMultiplierResolver->resolve($age);

        // Base do viajante: tarifa do destino × dias cobrados (já considerando o mínimo de 5 dias).
        $baseAmount = $dailyRate * $chargedDays;
        $afterAgeMultiplier = $baseAmount * $ageMultiplier;
        $subtotal = $afterAgeMultiplier;

        $adventureSportsRequested = $this->addOnChecker->has($traveler, AddOn::AdventureSports);
        $adventureSportsEligible = $this->adventureSportsEligibilityChecker->isEligible($age);
        $adventureSportsAmount = 0.0;
        $luggageRequested = $this->addOnChecker->has($traveler, AddOn::Luggage);
        $luggageAmount = 0.0;
        $appliedAddOns = [];

        // Add-on solicitado, mas fora da faixa etária: não cobra, apenas registra aviso para o frontend.
        if ($adventureSportsRequested && ! $adventureSportsEligible) {
            $warnings[] = [
                'code' => 'adventure_sports_age_out_of_range',
                'params' => [
                    'travelerName' => $traveler->name,
                    'minAge' => PricingConstants::MIN_ADVENTURE_SPORTS_AGE,
                    'maxAge' => PricingConstants::MAX_ADVENTURE_SPORTS_AGE,
                ],
            ];
        }

        // Esportes de aventura: acréscimo de 25% sobre o subtotal já ajustado pela idade.
        if ($adventureSportsRequested && $adventureSportsEligible) {
            $adventureSportsAmount = $subtotal * PricingConstants::ADVENTURE_SPORTS_RATE;
            $subtotal += $adventureSportsAmount;
            $appliedAddOns[] = AddOn::AdventureSports->value;
        }

        // Bagagem: valor fixo por dia cobrado, independente da idade.
        if ($luggageRequested) {
            $luggageAmount = PricingConstants::LUGGAGE_DAILY_RATE * $chargedDays;
            $subtotal += $luggageAmount;
            $appliedAddOns[] = AddOn::Luggage->value;
        }

        // rawSubtotal alimenta o desconto de grupo; subtotal exibido é arredondado por viajante (half up).
        $rawSubtotal = $subtotal;
        $roundedSubtotal = Money::roundHalfUp($subtotal);

        $travelerResult = new TravelerQuoteResult(
            name: $traveler->name,
            age: $age,
            subtotal: $roundedSubtotal,
            rawSubtotal: $rawSubtotal,
            appliedAddOns: $appliedAddOns,
        );

        // Detalhamento passo a passo exibido no modal "Como calculamos" do frontend.
        $breakdown = [
            'name' => $traveler->name,
            'birth_date' => $traveler->birthDate->toDateString(),
            'requested_add_ons' => array_map(static fn (AddOn $addOn) => $addOn->value, $traveler->addOns),
            'age_at_trip_start' => $age,
            'age_multiplier' => $ageMultiplier,
            'age_multiplier_label' => $this->ageMultiplierResolver->label($age, $ageMultiplier),
            'daily_rate' => $dailyRate,
            'charged_days' => $chargedDays,
            'base_amount' => $baseAmount,
            'base_formula' => 'daily_rate × charged_days',
            'after_age_multiplier' => $afterAgeMultiplier,
            'after_age_formula' => 'base_amount × age_multiplier',
            'adventure_sports_requested' => $adventureSportsRequested,
            'adventure_sports_eligible' => $adventureSportsEligible,
            'adventure_sports_amount' => $adventureSportsAmount,
            'adventure_sports_formula' => $adventureSportsRequested && $adventureSportsEligible
                ? 'after_age_multiplier × adventure_sports_rate'
                : null,
            'luggage_requested' => $luggageRequested,
            'luggage_amount' => $luggageAmount,
            'luggage_formula' => $luggageRequested ? 'luggage_daily_rate × charged_days' : null,
            'raw_subtotal' => $rawSubtotal,
            'rounded_subtotal' => $roundedSubtotal,
            'applied_add_ons' => $appliedAddOns,
        ];

        return [$travelerResult, $breakdown];
    }
}
