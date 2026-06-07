<?php

namespace App\Services\Quotes;

use App\DTO\QuoteRequestData;
use App\DTO\QuoteResult;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuotePersistenceService
{
    public function __construct(
        private readonly QuoteListService $quoteListService,
    ) {}

    public function store(User $user, QuoteRequestData $request, QuoteResult $result): Quote
    {
        return DB::transaction(function () use ($user, $request, $result) {
            $quote = Quote::create([
                'user_id' => $user->id,
                'destination' => $request->destination->value,
                'start_date' => $request->startDate->toDateString(),
                'end_date' => $request->endDate->toDateString(),
                'charged_days' => $result->chargedDays,
                'group_discount_percentage' => $result->groupDiscountPercentage,
                'final_total' => $result->finalTotal,
                'warnings' => $result->warnings,
                'calculation_breakdown' => $result->calculationBreakdown,
            ]);

            foreach ($result->travelers as $index => $traveler) {
                $travelerInput = $request->travelers[$index];

                $quote->travelers()->create([
                    'user_id' => $user->id,
                    'name' => $traveler->name,
                    'birth_date' => $travelerInput->birthDate->toDateString(),
                    'add_ons' => array_map(
                        static fn ($addOn) => $addOn->value,
                        $travelerInput->addOns,
                    ),
                    'age' => $traveler->age,
                    'subtotal' => $traveler->subtotal,
                    'applied_add_ons' => $traveler->appliedAddOns,
                ]);
            }

            $this->quoteListService->invalidateForUser($user->id);

            return $quote->load('travelers');
        });
    }

    public function update(Quote $quote, QuoteRequestData $request, QuoteResult $result): Quote
    {
        return DB::transaction(function () use ($quote, $request, $result) {
            $quote->update([
                'destination' => $request->destination->value,
                'start_date' => $request->startDate->toDateString(),
                'end_date' => $request->endDate->toDateString(),
                'charged_days' => $result->chargedDays,
                'group_discount_percentage' => $result->groupDiscountPercentage,
                'final_total' => $result->finalTotal,
                'warnings' => $result->warnings,
                'calculation_breakdown' => $result->calculationBreakdown,
            ]);

            $quote->travelers()->delete();

            foreach ($result->travelers as $index => $traveler) {
                $travelerInput = $request->travelers[$index];

                $quote->travelers()->create([
                    'user_id' => $quote->user_id,
                    'name' => $traveler->name,
                    'birth_date' => $travelerInput->birthDate->toDateString(),
                    'add_ons' => array_map(
                        static fn ($addOn) => $addOn->value,
                        $travelerInput->addOns,
                    ),
                    'age' => $traveler->age,
                    'subtotal' => $traveler->subtotal,
                    'applied_add_ons' => $traveler->appliedAddOns,
                ]);
            }

            $this->quoteListService->invalidateForUser($quote->user_id);

            return $quote->load('travelers');
        });
    }
}
