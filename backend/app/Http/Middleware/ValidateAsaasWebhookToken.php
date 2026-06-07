<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateAsaasWebhookToken
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.asaas.webhook_token');

        if (! is_string($configuredToken) || $configuredToken === '') {
            abort(503, 'Asaas webhook token is not configured.');
        }

        if ($request->header('asaas-access-token') !== $configuredToken) {
            abort(401);
        }

        return $next($request);
    }
}
