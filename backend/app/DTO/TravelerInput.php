<?php

namespace App\DTO;

use App\Enums\AddOn;
use Carbon\Carbon;

final readonly class TravelerInput
{
    /**
     * @param  list<AddOn>  $addOns
     */
    public function __construct(
        public string $name,
        public Carbon $birthDate,
        public array $addOns = [],
    ) {}
}
