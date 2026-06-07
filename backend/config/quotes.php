<?php

return [
    'cache' => [
        'enabled' => env('QUOTE_CACHE_ENABLED', true),
        'ttl_seconds' => (int) env('QUOTE_CACHE_TTL', 3600),
    ],
    'list_cache' => [
        'enabled' => env('QUOTE_LIST_CACHE_ENABLED', true),
        'ttl_seconds' => (int) env('QUOTE_LIST_CACHE_TTL', 300),
        'version_ttl_seconds' => (int) env('QUOTE_LIST_CACHE_VERSION_TTL', 86400),
    ],
];
