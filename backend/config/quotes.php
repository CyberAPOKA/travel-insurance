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
    'pix' => [
        'expiration_minutes' => (int) env('QUOTE_PIX_EXPIRATION_MINUTES', 60),
        'charge_percentage' => (float) env('QUOTE_PIX_CHARGE_PERCENTAGE', 0.1),
        'minimum_charge' => (float) env('QUOTE_PIX_MINIMUM_CHARGE', 0.01),
    ],
];
