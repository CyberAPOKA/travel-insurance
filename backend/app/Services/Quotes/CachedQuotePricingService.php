<?php

namespace App\Services\Quotes;

use App\DTO\QuoteRequestData;
use App\DTO\QuoteResult;
use Illuminate\Support\Facades\Cache;

class CachedQuotePricingService
{
    public function __construct(
        private readonly QuotePricingService $quotePricingService,
    ) {}

    public function calculate(QuoteRequestData $request): QuoteResult
    {
        if (! config('quotes.cache.enabled', true)) {
            return $this->quotePricingService->calculate($request);
        }

        $cacheKey = QuoteCacheKey::build($request);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return QuoteResult::fromArray($cached);
        }

        $result = $this->quotePricingService->calculate($request);
        Cache::put($cacheKey, $result->toArray(), config('quotes.cache.ttl_seconds', 3600));

        return $result;
    }
}
