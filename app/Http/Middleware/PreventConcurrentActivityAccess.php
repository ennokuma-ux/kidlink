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

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $token = $request->session()->getId();
        $userId = $user->getAuthIdentifier();
        $routeName = optional($request->route())->getName();

        if ($routeName === 'teacher.activities.lock.release') {
            DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where('lock_token', $token)
                ->delete();

            return response()->json(['ok' => true]);
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
                return response()->json(['ok' => false, 'message' => 'This Activity Planner session is no longer active on this device.'], 409);
            }

            return response()->json(['ok' => true]);
        }

        if ($routeName === 'teacher.activities.index' && $request->isMethod('GET')) {
            // Acquire the page lease atomically. A read-then-write sequence with
            // lockForUpdate() is not reliable on every supported database
            // (notably SQLite), and can let two first-time requests both pass.
            // The unique (user_id, page_key) index guarantees one lock row;
            // this conditional UPDATE only takes it when it is ours or expired.
            $now = now();
            DB::table('user_page_locks')->insertOrIgnore([
                'user_id' => $userId,
                'page_key' => self::PAGE_KEY,
                'lock_token' => $token,
                'last_seen_at' => $now,
                'expires_at' => $now->copy()->addSeconds(self::LEASE_SECONDS),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where(function ($query) use ($token, $now) {
                    $query->where('lock_token', $token)
                        ->orWhere('expires_at', '<=', $now);
                })
                ->update([
                    'lock_token' => $token,
                    'last_seen_at' => $now,
                    'expires_at' => $now->copy()->addSeconds(self::LEASE_SECONDS),
                    'updated_at' => $now,
                ]);

            // Some database drivers report zero affected rows when the row
            // already contains these values. Confirm ownership from the row
            // itself instead of relying on the UPDATE row count.
            $ownsLock = DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where('lock_token', $token)
                ->where('expires_at', '>', now())
                ->exists();

            if (! $ownsLock) {
                return response()->view('errors.concurrent-access', [
                    'retryUrl' => route('teacher.activities.index', $request->query()),
                ], 409);
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
                    ], 409);
                }

                return response()->view('errors.concurrent-access', [
                    'retryUrl' => route('teacher.activities.index'),
                ], 409);
            }
        }

        return $next($request);
    }
}
