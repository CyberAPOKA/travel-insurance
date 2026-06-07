<?php

namespace App\DTO;

final readonly class TravelerQuoteResult
{
    /**
     * @param  list<string>  $appliedAddOns
     */
    public function __construct(
        public string $name,
        public int $age,
        public float $subtotal,
        public float $rawSubtotal,
        public array $appliedAddOns,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'age' => $this->age,
            'subtotal' => $this->subtotal,
            'applied_add_ons' => $this->appliedAddOns,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $subtotal = (float) $data['subtotal'];

        return new self(
            name: $data['name'],
            age: $data['age'],
            subtotal: $subtotal,
            rawSubtotal: $subtotal,
            appliedAddOns: $data['applied_add_ons'],
        );
    }
}
