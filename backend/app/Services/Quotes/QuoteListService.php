<?php

namespace App\Services\Quotes;

use App\DTO\QuoteListPageResult;
use App\Filters\Quotes\QuoteListFilter;
use App\Models\Quote;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class QuoteListService
{
    public function __construct(
        private readonly QuoteListFilter $quoteListFilter,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(
        int $userId,
        array $filters,
        int $page,
        int $perPage,
    ): QuoteListPageResult {
        if (! config('quotes.list_cache.enabled', true)) {
            return new QuoteListPageResult(
                $this->queryFromDatabase($userId, $filters, $page, $perPage),
                QuoteListPageResult::SOURCE_DATABASE,
            );
        }

        $cacheKey = QuoteListCacheKey::build(
            $userId,
            $this->cacheVersion($userId),
            $filters,
            $page,
            $perPage,
        );

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return new QuoteListPageResult(
                $this->paginatorFromCache($cached, $page, $perPage),
                QuoteListPageResult::SOURCE_CACHE,
            );
        }

        $paginator = $this->queryFromDatabase($userId, $filters, $page, $perPage);

        Cache::put(
            $cacheKey,
            $this->serializePaginator($paginator),
            config('quotes.list_cache.ttl_seconds', 300),
        );

        return new QuoteListPageResult($paginator, QuoteListPageResult::SOURCE_DATABASE);
    }

    public function invalidateForUser(int $userId): void
    {
        $versionKey = QuoteListCacheKey::versionKey($userId);
        $currentVersion = $this->cacheVersion($userId);

        Cache::put(
            $versionKey,
            $currentVersion + 1,
            config('quotes.list_cache.version_ttl_seconds', 86400),
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function queryFromDatabase(
        int $userId,
        array $filters,
        int $page,
        int $perPage,
    ): LengthAwarePaginator {
        return $this->quoteListFilter
            ->apply(
                Quote::query()->forUser($userId),
                $filters,
            )
            ->withCount('travelers')
            ->latestForUser()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param  array<string, mixed>  $cached
     */
    private function paginatorFromCache(array $cached, int $page, int $perPage): LengthAwarePaginator
    {
        $items = collect($cached['items'] ?? [])
            ->map(function (array $attributes): Quote {
                $quote = new Quote;
                $quote->forceFill($attributes);
                $quote->travelers_count = (int) ($attributes['travelers_count'] ?? 0);
                $quote->syncOriginal();

                return $quote;
            })
            ->all();

        return new LengthAwarePaginator(
            $items,
            (int) ($cached['total'] ?? 0),
            (int) ($cached['per_page'] ?? $perPage),
            (int) ($cached['current_page'] ?? $page),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePaginator(LengthAwarePaginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'items' => collect($paginator->items())
                ->map(static function (Quote $quote): array {
                    return [
                        ...$quote->toArray(),
                        'travelers_count' => (int) $quote->travelers_count,
                    ];
                })
                ->all(),
        ];
    }

    private function cacheVersion(int $userId): int
    {
        return (int) Cache::get(QuoteListCacheKey::versionKey($userId), 1);
    }
}
