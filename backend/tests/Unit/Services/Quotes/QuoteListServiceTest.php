<?php

namespace Tests\Unit\Services\Quotes;

use App\DTO\QuoteListPageResult;
use App\Filters\Quotes\QuoteListFilter;
use App\Models\Quote;
use App\Models\User;
use App\Services\Quotes\QuoteListCacheKey;
use App\Services\Quotes\QuoteListService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuoteListServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_database_source_on_first_request_and_cache_on_second(): void
    {
        config([
            'quotes.list_cache.enabled' => true,
            'quotes.list_cache.ttl_seconds' => 3600,
            'cache.default' => 'array',
        ]);

        $user = User::factory()->create();

        Quote::query()->create([
            'user_id' => $user->id,
            'destination' => 'NATIONAL',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-14',
            'charged_days' => 5,
            'group_discount_percentage' => 0,
            'final_total' => 50,
            'warnings' => [],
            'calculation_breakdown' => null,
        ]);

        $service = new QuoteListService(app(QuoteListFilter::class));

        $first = $service->paginate($user->id, [], 1, 10);
        $second = $service->paginate($user->id, [], 1, 10);

        $this->assertSame(QuoteListPageResult::SOURCE_DATABASE, $first->source);
        $this->assertSame(QuoteListPageResult::SOURCE_CACHE, $second->source);
        $this->assertCount(1, $first->paginator->items());
        $this->assertCount(1, $second->paginator->items());
    }

    #[Test]
    public function it_invalidates_cached_lists_for_a_user(): void
    {
        config([
            'quotes.list_cache.enabled' => true,
            'quotes.list_cache.ttl_seconds' => 3600,
            'cache.default' => 'array',
        ]);

        $user = User::factory()->create();

        Quote::query()->create([
            'user_id' => $user->id,
            'destination' => 'NATIONAL',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-14',
            'charged_days' => 5,
            'group_discount_percentage' => 0,
            'final_total' => 50,
            'warnings' => [],
            'calculation_breakdown' => null,
        ]);

        $service = new QuoteListService(app(QuoteListFilter::class));

        $service->paginate($user->id, [], 1, 10);
        $service->invalidateForUser($user->id);
        $afterInvalidation = $service->paginate($user->id, [], 1, 10);

        $this->assertSame(QuoteListPageResult::SOURCE_DATABASE, $afterInvalidation->source);
        $this->assertSame(2, Cache::get(QuoteListCacheKey::versionKey($user->id)));
    }
}
