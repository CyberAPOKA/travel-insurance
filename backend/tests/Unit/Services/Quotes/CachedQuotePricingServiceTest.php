<?php

namespace Tests\Unit\Services\Quotes;

use App\DTO\QuoteRequestData;
use App\DTO\TravelerInput;
use App\Enums\DestinationZone;
use App\Services\Quotes\CachedQuotePricingService;
use App\Services\Quotes\QuoteCacheKey;
use App\Services\Quotes\QuotePricingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CachedQuotePricingServiceTest extends TestCase
{
    #[Test]
    public function it_caches_quote_results_by_request_payload(): void
    {
        config(['quotes.cache.enabled' => true, 'quotes.cache.ttl_seconds' => 3600]);
        config(['cache.default' => 'array']);

        $request = new QuoteRequestData(
            destination: DestinationZone::National,
            startDate: Carbon::parse('2026-07-10'),
            endDate: Carbon::parse('2026-07-14'),
            travelers: [
                new TravelerInput(
                    name: 'Alice',
                    birthDate: Carbon::parse('1990-03-15'),
                ),
            ],
        );

        $service = new CachedQuotePricingService(new QuotePricingService);

        $firstResult = $service->calculate($request);
        $secondResult = $service->calculate($request);

        $this->assertSame($firstResult->toArray(), $secondResult->toArray());
        $this->assertNotNull(Cache::get(QuoteCacheKey::build($request)));
    }
}
