<?php

namespace App\Services\Quotes;

use App\DTO\QuoteRequestData;
use App\DTO\QuoteResult;
use App\DTO\TravelerInput;
use App\DTO\TravelerQuoteResult;
use App\Enums\AddOn;
use App\ValueObjects\Money;
use Carbon\Carbon;

class QuotePricingService
{
    // Dias mínimos cobrados
    private const MIN_CHARGED_DAYS = 5;

    // Taxa diária de bagagem
    private const LUGGAGE_DAILY_RATE = 3.00;

    // Taxa de esportes de aventura
    private const ADVENTURE_SPORTS_RATE = 0.25;

    // Número mínimo de viajantes para aplicar desconto de grupo    
    private const GROUP_DISCOUNT_THRESHOLD = 5;

    // Percentual de desconto de grupo (10%)
    private const GROUP_DISCOUNT_RATE = 0.10;

    // Faixa etária mínima para aplicar esportes de aventura
    private const MIN_ADVENTURE_SPORTS_AGE = 18;

    // Faixa etária máxima para aplicar esportes de aventura
    private const MAX_ADVENTURE_SPORTS_AGE = 64;

    /**
     * Calcula o resultado de uma cotação de viagem.
     *
     * @param  QuoteRequestData  $request
     * @return QuoteResult
     */
    public function calculate(QuoteRequestData $request): QuoteResult
    {
        $tripDays = (int) $request->startDate->diffInDays($request->endDate) + 1;
        $chargedDays = $this->resolveChargedDays($request->startDate, $request->endDate);
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
                static fn(TravelerQuoteResult $result): float => $result->rawSubtotal,
                $travelerResults,
            ),
        );

        $groupDiscountPercentage = $this->resolveGroupDiscountPercentage(count($request->travelers));
        $discountAmount = $groupTotal * ($groupDiscountPercentage / 100);
        $finalTotal = Money::roundHalfUp($groupTotal - $discountAmount);

        return new QuoteResult(
            chargedDays: $chargedDays,
            travelers: $travelerResults,
            warnings: $warnings,
            groupDiscountPercentage: $groupDiscountPercentage,
            finalTotal: $finalTotal,
            calculationBreakdown: [
                'constants' => $this->pricingConstants(),
                'trip' => [
                    'destination' => $request->destination->value,
                    'daily_rate' => $dailyRate,
                    'start_date' => $request->startDate->toDateString(),
                    'end_date' => $request->endDate->toDateString(),
                    'trip_days' => $tripDays,
                    'charged_days' => $chargedDays,
                    'min_charged_days' => self::MIN_CHARGED_DAYS,
                    'min_charged_days_applied' => $tripDays < self::MIN_CHARGED_DAYS,
                    'charged_days_formula' => 'max(min_charged_days, trip_days)',
                ],
                'travelers' => $travelerBreakdowns,
                'summary' => [
                    'travelers_count' => count($request->travelers),
                    'group_subtotal_before_discount' => $groupTotal,
                    'group_discount_threshold' => self::GROUP_DISCOUNT_THRESHOLD,
                    'group_discount_percentage' => $groupDiscountPercentage,
                    'group_discount_amount' => $discountAmount,
                    'final_total' => $finalTotal,
                    'rounding' => 'half_up',
                ],
            ],
        );
    }

    /**
     * @return array<string, float|int>
     */
    private function pricingConstants(): array
    {
        return [
            'min_charged_days' => self::MIN_CHARGED_DAYS,
            'luggage_daily_rate' => self::LUGGAGE_DAILY_RATE,
            'adventure_sports_rate' => self::ADVENTURE_SPORTS_RATE,
            'group_discount_threshold' => self::GROUP_DISCOUNT_THRESHOLD,
            'group_discount_percentage' => (int) (self::GROUP_DISCOUNT_RATE * 100),
            'adventure_sports_min_age' => self::MIN_ADVENTURE_SPORTS_AGE,
            'adventure_sports_max_age' => self::MAX_ADVENTURE_SPORTS_AGE,
        ];
    }

    private function resolveChargedDays(Carbon $startDate, Carbon $endDate): int
    {
        $tripDays = (int) $startDate->diffInDays($endDate) + 1;

        return max(self::MIN_CHARGED_DAYS, $tripDays);
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
        $age = $this->calculateAgeAtDate($traveler->birthDate, $startDate);
        $ageMultiplier = $this->resolveAgeMultiplier($age);
        $baseAmount = $dailyRate * $chargedDays;
        $afterAgeMultiplier = $baseAmount * $ageMultiplier;
        $subtotal = $afterAgeMultiplier;
        $adventureSportsRequested = $this->hasAddOn($traveler, AddOn::AdventureSports);
        $adventureSportsEligible = $this->isAdventureSportsEligible($age);
        $adventureSportsAmount = 0.0;
        $luggageRequested = $this->hasAddOn($traveler, AddOn::Luggage);
        $luggageAmount = 0.0;
        $appliedAddOns = [];

        if ($adventureSportsRequested && ! $adventureSportsEligible) {
            $warnings[] = [
                'code' => 'adventure_sports_age_out_of_range',
                'params' => [
                    'travelerName' => $traveler->name,
                    'minAge' => self::MIN_ADVENTURE_SPORTS_AGE,
                    'maxAge' => self::MAX_ADVENTURE_SPORTS_AGE,
                ],
            ];
        }

        if ($adventureSportsRequested && $adventureSportsEligible) {
            $adventureSportsAmount = $subtotal * self::ADVENTURE_SPORTS_RATE;
            $subtotal += $adventureSportsAmount;
            $appliedAddOns[] = AddOn::AdventureSports->value;
        }

        if ($luggageRequested) {
            $luggageAmount = self::LUGGAGE_DAILY_RATE * $chargedDays;
            $subtotal += $luggageAmount;
            $appliedAddOns[] = AddOn::Luggage->value;
        }

        $rawSubtotal = $subtotal;
        $roundedSubtotal = Money::roundHalfUp($subtotal);

        $travelerResult = new TravelerQuoteResult(
            name: $traveler->name,
            age: $age,
            subtotal: $roundedSubtotal,
            rawSubtotal: $rawSubtotal,
            appliedAddOns: $appliedAddOns,
        );

        $breakdown = [
            'name' => $traveler->name,
            'birth_date' => $traveler->birthDate->toDateString(),
            'requested_add_ons' => array_map(static fn(AddOn $addOn) => $addOn->value, $traveler->addOns),
            'age_at_trip_start' => $age,
            'age_multiplier' => $ageMultiplier,
            'age_multiplier_label' => $this->resolveAgeMultiplierLabel($age, $ageMultiplier),
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

    private function resolveAgeMultiplierLabel(int $age, float $multiplier): string
    {
        if ($age <= 17) {
            return sprintf('%sx (0-17)', $multiplier);
        }

        if ($age <= 64) {
            return sprintf('%sx (18-64)', $multiplier);
        }

        return sprintf('%sx (65+)', $multiplier);
    }

    private function calculateAgeAtDate(Carbon $birthDate, Carbon $referenceDate): int
    {
        return (int) $birthDate->diff($referenceDate)->y;
    }

    private function resolveAgeMultiplier(int $age): float
    {
        if ($age <= 17) {
            return 0.5;
        }

        if ($age <= 64) {
            return 1.0;
        }

        return 2.0;
    }

    private function isAdventureSportsEligible(int $age): bool
    {
        return $age >= self::MIN_ADVENTURE_SPORTS_AGE && $age <= self::MAX_ADVENTURE_SPORTS_AGE;
    }

    private function resolveGroupDiscountPercentage(int $travelerCount): int
    {
        return $travelerCount >= self::GROUP_DISCOUNT_THRESHOLD
            ? (int) (self::GROUP_DISCOUNT_RATE * 100)
            : 0;
    }

    private function hasAddOn(TravelerInput $traveler, AddOn $addOn): bool
    {
        return in_array($addOn, $traveler->addOns, true);
    }
}
