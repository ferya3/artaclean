<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale resolution order: an explicit ?lang= switch (which is remembered),
 * then the session, then the signed-in user's preference, then the default.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('site.locales'));

        $locale = $request->query('lang');

        if (! in_array($locale, $supported, true)) {
            $locale = $request->session()->get('locale')
                ?? $request->user()?->locale
                ?? config('app.locale');
        } else {
            $request->session()->put('locale', $locale);
        }

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.fallback_locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
