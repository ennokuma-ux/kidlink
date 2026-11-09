<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
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
        $this->renderable(function (Throwable $exception, $request) {
            // Let Laravel return its normal field-by-field validation payload.
            if (! $request->expectsJson() || $exception instanceof ValidationException) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            $messages = [
                401 => 'Your session is not signed in. Please sign in and try again.',
                403 => 'You do not have permission to perform this action.',
                404 => 'The requested page or record could not be found.',
                419 => 'Your session expired. Refresh the page and try again.',
                429 => 'Too many requests. Please wait a moment and try again.',
                500 => 'Something went wrong while processing your request. Please try again.',
                503 => 'The service is temporarily unavailable. Please try again shortly.',
            ];

            return response()->json([
                'message' => $messages[$status] ?? 'The request could not be completed. Please try again.',
            ], $status);
        });
    }
}
