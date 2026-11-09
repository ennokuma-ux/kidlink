<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Drops a second identical POST that arrives right after the first.
 *
 * A double click or a double tap on a slow connection can send the same form
 * twice before the page has had time to change, which would create the record
 * twice. The browser-side lock in the page loader covers the normal case; this
 * is the server-side backstop for when two requests still get through.
 *
 * Only POST is guarded: PUT and DELETE are repeat-safe by nature.
 */
class PreventDuplicateSubmission
{
    /** How long an identical request counts as a duplicate, in seconds. */
    private const WINDOW_SECONDS = 4;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST')) {
            return $next($request);
        }

        $key = 'dup-submit:'.sha1(implode('|', [
            $request->user()?->getAuthIdentifier() ?? $request->ip(),
            $request->path(),
            json_encode($request->except(['_token'])),
            // Uploaded files are not part of input(); fold in their names and sizes.
            json_encode(collect($request->allFiles())->flatten()->map(
                fn ($f) => [$f->getClientOriginalName(), $f->getSize()]
            )->all()),
        ]));

        // Cache::add is atomic: only the first request in the window gets true.
        if (! Cache::add($key, true, self::WINDOW_SECONDS)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Already submitted.'], 409);
            }

            return redirect()->back()->withInput()->with('info', 'This form was already submitted. Please wait a moment before trying again.');
        }

        return $next($request);
    }
}
