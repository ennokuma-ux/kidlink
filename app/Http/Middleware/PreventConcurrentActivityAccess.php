<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows one active device/session per teacher account in the Activity Planner.
 * A short lease is refreshed by the planner page and naturally expires if a
 * browser closes unexpectedly or loses connectivity.
 */
class PreventConcurrentActivityAccess
{
    private const PAGE_KEY = 'teacher-activities';
    private const LEASE_SECONDS = 90;
    private const DEVICE_COOKIE = 'kidlink_activity_device';
    private const DEVICE_COOKIE_MINUTES = 43200; // 30 days

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // Laravel session IDs are not a reliable device identity. Use a separate,
        // random, per-browser cookie so two physical devices get different lock tokens.
        $token = (string) $request->cookie(self::DEVICE_COOKIE, '');
        if (! preg_match('/^[a-f0-9]{64}$/', $token)) {
            $token = bin2hex(random_bytes(32));
        }
        $userId = $user->getAuthIdentifier();
        $routeName = optional($request->route())->getName();

        if ($routeName === 'teacher.activities.lock.release') {
            DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where('lock_token', $token)
                ->delete();

            return response()->json(['ok' => true])->withCookie($this->deviceCookie($request, $token));
        }

        if ($routeName === 'teacher.activities.lock.heartbeat') {
            $updated = DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where('lock_token', $token)
                ->where('expires_at', '>', now())
                ->update([
                    'last_seen_at' => now(),
                    'expires_at' => now()->addSeconds(self::LEASE_SECONDS),
                    'updated_at' => now(),
                ]);

            if (! $updated) {
                return response()->json(['ok' => false, 'message' => 'This Activity Planner is active on another device.'], 409)
                    ->withCookie($this->deviceCookie($request, $token));
            }

            return response()->json(['ok' => true])->withCookie($this->deviceCookie($request, $token));
        }

        if ($routeName === 'teacher.activities.index' && $request->isMethod('GET')) {
            $lockedByAnotherDevice = DB::transaction(function () use ($userId, $token) {
                DB::table('user_page_locks')->insertOrIgnore([
                    'user_id' => $userId,
                    'page_key' => self::PAGE_KEY,
                    'lock_token' => $token,
                    'last_seen_at' => now(),
                    'expires_at' => now()->addSeconds(self::LEASE_SECONDS),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $lock = DB::table('user_page_locks')
                    ->where('user_id', $userId)
                    ->where('page_key', self::PAGE_KEY)
                    ->lockForUpdate()
                    ->first();

                if ($lock && $lock->lock_token !== $token && \Illuminate\Support\Carbon::parse($lock->expires_at)->isFuture()) {
                    return true;
                }

                DB::table('user_page_locks')
                    ->where('user_id', $userId)
                    ->where('page_key', self::PAGE_KEY)
                    ->update([
                        'lock_token' => $token,
                        'last_seen_at' => now(),
                        'expires_at' => now()->addSeconds(self::LEASE_SECONDS),
                        'updated_at' => now(),
                    ]);

                return false;
            });

            if ($lockedByAnotherDevice) {
                return response()->view('errors.concurrent-access', [
                    'retryUrl' => route('teacher.activities.index', $request->query()),
                ], 409)->withCookie($this->deviceCookie($request, $token));
            }
        } else {
            $ownsLock = DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where('lock_token', $token)
                ->where('expires_at', '>', now())
                ->exists();

            if (! $ownsLock) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'The Activity Planner is active on another device. Open it on that device or try again later.',
                    ], 409)->withCookie($this->deviceCookie($request, $token));
                }

                return response()->view('errors.concurrent-access', [
                    'retryUrl' => route('teacher.activities.index'),
                ], 409)->withCookie($this->deviceCookie($request, $token));
            }
        }

        $response = $next($request);

        // Persist the device identity before the next request (including refreshes).
        return $response->withCookie($this->deviceCookie($request, $token));
    }

    private function deviceCookie(Request $request, string $token)
    {
        return cookie(
            self::DEVICE_COOKIE,
            $token,
            self::DEVICE_COOKIE_MINUTES,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax'
        );
    }
}
