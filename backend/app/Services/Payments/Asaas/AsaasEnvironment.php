<?php

namespace App\Services\Payments\Asaas;

class AsaasEnvironment
{
    public static function isSandbox(): bool
    {
        $baseUrl = strtolower((string) config('services.asaas.base_url', ''));

        return str_contains($baseUrl, 'sandbox');
    }
}
