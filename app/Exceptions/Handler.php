<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // A 405 means a URL that only accepts another method (for example a
        // POST-only background endpoint) was opened as a normal page. Log where
        // it came from so it can be traced, then show a clear page.
        $this->renderable(function (MethodNotAllowedHttpException $exception, $request) {
            $reference = strtoupper(Str::random(8));

            Log::warning('Method not allowed.', [
                'reference' => $reference,
                'method' => $request->method(),
                'allowed' => $exception->getHeaders()['Allow'] ?? null,
                'url' => $request->fullUrl(),
                'referer' => $request->headers->get('referer'),
                'user_id' => optional($request->user())->getAuthIdentifier(),
                'ip' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 160, ''),
            ]);

            if ($request->expectsJson()) {
                return null; // falls through to the JSON message below
            }

            return response()->view('errors.405', [
                'reference' => $reference,
                'allowed' => $exception->getHeaders()['Allow'] ?? null,
            ], 405, $exception->getHeaders());
        });

        $this->renderable(function (Throwable $exception, $request) {
            // Let Laravel return its normal field-by-field validation payload.
            if (! $request->expectsJson() || $exception instanceof ValidationException) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            $messages = [
                400 => 'The request contains invalid information. Check your entries and try again.',
                401 => 'Your session is not signed in. Please sign in and try again.',
                403 => 'You do not have permission to perform this action.',
                404 => 'The requested page or record could not be found. It may have been removed.',
                405 => 'This action is not supported here. Refresh the page and try again.',
                408 => 'The request took too long. Check your connection and try again.',
                413 => 'The submitted content is too large. Reduce the file or message size and try again.',
                419 => 'Your session expired. Refresh the page and try again.',
                422 => 'Some submitted information is invalid. Check the highlighted fields and try again.',
                429 => 'Too many requests. Please wait a moment and try again.',
                500 => 'Something went wrong while processing your request. Please try again. If it continues, contact an administrator.',
                502 => 'The application server received an invalid response. Please retry in a moment.',
                503 => 'The service is temporarily unavailable. Please try again shortly.',
                504 => 'The server took too long to respond. Check your connection and try again.',
            ];

            return response()->json([
                'message' => $messages[$status] ?? 'The request could not be completed. Please try again.',
            ], $status);
        });
    }
}
