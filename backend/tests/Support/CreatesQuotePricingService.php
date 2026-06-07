<?php

namespace Tests\Support;

use App\Services\Quotes\PricingRules\AdventureSportsEligibilityChecker;
use App\Services\Quotes\PricingRules\AgeCalculator;
use App\Services\Quotes\PricingRules\AgeMultiplierResolver;
use App\Services\Quotes\PricingRules\ChargedDaysCalculator;
use App\Services\Quotes\PricingRules\GroupDiscountResolver;
use App\Services\Quotes\QuotePricingService;
use App\Services\Quotes\Support\AddOnChecker;

trait CreatesQuotePricingService
{
    protected function makeQuotePricingService(): QuotePricingService
    {
        return new QuotePricingService(
            new ChargedDaysCalculator,
            new AgeCalculator,
            new AgeMultiplierResolver,
            new AdventureSportsEligibilityChecker,
            new GroupDiscountResolver,
            new AddOnChecker,
        );
    }
}
