<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromRequest
{
    /** @var list<string> */
    private const SUPPORTED = ['en', 'pt'];

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('X-App-Locale') ?? $request->header('Accept-Language', '');
        $locale = strtolower(substr(trim(explode(',', $header)[0]), 0, 2));

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
