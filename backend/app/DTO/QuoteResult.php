<?php

namespace App\DTO;

final readonly class QuoteResult
{
    /**
     * @param  list<TravelerQuoteResult>  $travelers
     * @param  list<array{code: string, params: array<string, int|string>}>  $warnings
     */
    public function __construct(
        public int $chargedDays,
        public array $travelers,
        public array $warnings,
        public int $groupDiscountPercentage,
        public float $finalTotal,
        public ?array $calculationBreakdown = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'charged_days' => $this->chargedDays,
            'travelers' => array_map(
                static fn (TravelerQuoteResult $traveler) => $traveler->toArray(),
                $this->travelers,
            ),
            'warnings' => $this->warnings,
            'group_discount_percentage' => $this->groupDiscountPercentage,
            'final_total' => $this->finalTotal,
            'calculation_breakdown' => $this->calculationBreakdown,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            chargedDays: $data['charged_days'],
            travelers: array_map(
                static fn (array $traveler) => TravelerQuoteResult::fromArray($traveler),
                $data['travelers'],
            ),
            warnings: $data['warnings'],
            groupDiscountPercentage: $data['group_discount_percentage'],
            finalTotal: (float) $data['final_total'],
            calculationBreakdown: $data['calculation_breakdown'] ?? null,
        );
    }
}
