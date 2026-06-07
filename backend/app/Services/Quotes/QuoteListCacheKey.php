<?php

namespace App\Services\Quotes;

final class QuoteListCacheKey
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public static function build(
        int $userId,
        int $version,
        array $filters,
        int $page,
        int $perPage,
    ): string {
        $payload = [
            'user_id' => $userId,
            'version' => $version,
            'filters' => $filters,
            'page' => $page,
            'per_page' => $perPage,
        ];

        return 'quote-list:'.hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    public static function versionKey(int $userId): string
    {
        return "quote-list-version:{$userId}";
    }
}
