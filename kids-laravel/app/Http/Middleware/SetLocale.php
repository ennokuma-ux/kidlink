<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's chosen language to every request.
 *
 * The choice is stored in the session by the switcher and mirrored into a
 * cookie so it survives the session expiring. Anything unsupported falls back
 * to the site default rather than trusting whatever arrived in the cookie.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->session()->get('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale');

        $locale = in_array($requested, array_keys(config('app.supported_locales', [])), true)
            ? $requested
            : config('app.locale');

        App::setLocale($locale);

        return $next($request);
    }
}
