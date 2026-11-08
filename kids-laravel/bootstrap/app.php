<?php

use App\Exceptions\MissingDatabaseDriverException;
use App\Support\Database\PdoDrivers;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'active' => \App\Http\Middleware\EnsureAccountIsActive::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
        ]);

        // Appended to the web group so the chosen language is applied to every
        // page, including the login screen. Must run after StartSession.
        $middleware->web([
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            $drivers = app(PdoDrivers::class);

            if (! MissingDatabaseDriverException::matches($e, $drivers)) {
                return null;
            }

            $exception = $e instanceof MissingDatabaseDriverException
                ? $e
                : MissingDatabaseDriverException::forDefaultConnection($drivers);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'required_extension' => $exception->extensionName(),
                    'php_binary' => $exception->runningBinary(),
                ], 503);
            }

            return response()->view('errors.missing-database-driver', [
                'exception' => $exception,
                'availableDrivers' => $drivers->available(),
                'suggested' => $exception->suggestedBinary(),
            ], 503);
        });

        // A stale CSRF token is almost always an expired session, not an attack.
        // Laravel's default is a bare "419 Page Expired", which tells the user
        // nothing about what to do next.
        //
        // Typed on HttpException, not TokenMismatchException: the framework rewrites
        // TokenMismatchException into HttpException(419) before render callbacks run.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            // A live, authenticated session that still rejects the token is a real
            // CSRF problem and should keep the plain 419 so it stays obvious during
            // development. No authenticated session means the old one is simply gone.
            if (auth()->check()) {
                return null;
            }

            // Remember where they were so signing back in returns them to it.
            $attempted = $request->fullUrl();

            if (! $request->is('login', 'logout')) {
                $request->session()->put('url.intended', $attempted);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please sign in again.',
                    'login_url' => route('login'),
                ], 419);
            }

            return response()->view('errors.session-expired', [
                'lifetimeMinutes' => (int) config('session.lifetime'),
                'attempted' => $attempted,
                'loginUrl' => route('login'),
            ], 419);
        });
    })->create();
