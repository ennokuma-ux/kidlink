<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows only one browser session per user account to hold the Activity Planner.
 * The database's unique (user_id, page_key) key and conditional UPDATE make
 * claiming an expired lease atomic across separate devices and concurrent requests.
 */
class PreventConcurrentActivityAccess
{
    private const PAGE_KEY = 'teacher-activities';
    private const LEASE_SECONDS = 90;
    private const SESSION_TOKEN_KEY = 'teacher_activity_planner_lock_token';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // Keep a stable, random token for this browser session. Different devices
        // logging into the same account receive different Laravel sessions/tokens.
        $token = $request->session()->get(self::SESSION_TOKEN_KEY);
        if (! is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $request->session()->put(self::SESSION_TOKEN_KEY, $token);
        }

        $userId = $user->getAuthIdentifier();
        $routeName = $request->route()?->getName();
        $now = now();

        if ($routeName === 'teacher.activities.lock.release') {
            DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where('lock_token', $token)
                ->delete();

            return response()->json(['ok' => true]);
        }

        if ($routeName === 'teacher.activities.index' && $request->isMethod('GET')) {
            // First claimant inserts the unique row. Later claimants cannot replace
            // it while its lease is valid. An expired row can be reclaimed by only
            // one request because the expiry condition is part of the UPDATE.
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

            $ownsLock = DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where('lock_token', $token)
                ->where('expires_at', '>', $now)
                ->exists();

            if (! $ownsLock) {
                return response()->view('errors.concurrent-access', [
                    'retryUrl' => route('teacher.activities.index', $request->query()),
                ], 409);
            }

            return $next($request);
        }

        // All planner actions, including heartbeat and edits, require ownership.
        $ownsLock = DB::table('user_page_locks')
            ->where('user_id', $userId)
            ->where('page_key', self::PAGE_KEY)
            ->where('lock_token', $token)
            ->where('expires_at', '>', $now)
            ->exists();

        if (! $ownsLock) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'The Activity Planner is already open on another device for this account.',
                ], 409);
            }

            return response()->view('errors.concurrent-access', [
                'retryUrl' => route('teacher.activities.index'),
            ], 409);
        }

        if ($routeName === 'teacher.activities.lock.heartbeat') {
            DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', self::PAGE_KEY)
                ->where('lock_token', $token)
                ->update([
                    'last_seen_at' => $now,
                    'expires_at' => $now->copy()->addSeconds(self::LEASE_SECONDS),
                    'updated_at' => $now,
                ]);

            return response()->json(['ok' => true]);
        }

        return $next($request);
    }
}
