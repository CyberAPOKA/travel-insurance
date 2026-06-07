<?php

namespace App\Services\Quotes\Support;

use App\DTO\TravelerInput;
use App\Enums\AddOn;

class AddOnChecker
{
    /**
     * Verifica se o viajante solicitou um add-on específico.
     */
    public function has(TravelerInput $traveler, AddOn $addOn): bool
    {
        return in_array($addOn, $traveler->addOns, true);
    }
}
