<?php

namespace App\DTO;

use Illuminate\Pagination\LengthAwarePaginator;

final class QuoteListPageResult
{
    public const SOURCE_DATABASE = 'database';

    public const SOURCE_CACHE = 'cache';

    public function __construct(
        public readonly LengthAwarePaginator $paginator,
        public readonly string $source,
    ) {}
}
