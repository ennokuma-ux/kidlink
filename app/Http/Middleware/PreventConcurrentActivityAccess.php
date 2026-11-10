<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces one active browser device per account on selected teacher/parent pages.
 * The device token is a long-lived, HttpOnly cookie (not the Laravel session ID),
 * so two devices signed into the same account receive different lock identities.
 */
class PreventConcurrentActivityAccess
{
    private const DEVICE_COOKIE = 'kidlink_device_token';
    private const ACTIVE_PAGE_COOKIE = 'kidlink_active_page';
    private const LEASE_SECONDS = 90;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();
        $deviceToken = $request->cookie(self::DEVICE_COOKIE);
        $newDeviceToken = ! is_string($deviceToken) || $deviceToken === '';
        if ($newDeviceToken) {
            $deviceToken = Str::random(64);
        }

        // Lock maintenance endpoints are handled separately from page navigation.
        if ($routeName === 'teacher.activities.lock.heartbeat') {
            return $this->heartbeat($request, $user->getAuthIdentifier(), $deviceToken, 'teacher-activities');
        }

        if ($routeName === 'teacher.activities.lock.release') {
            $this->release($user->getAuthIdentifier(), $deviceToken, 'teacher-activities');
            return $this->withDeviceCookie(response()->json(['ok' => true]), $deviceToken, $request);
        }

        if ($routeName === 'parent.page-lock.heartbeat') {
            $pageKey = $this->parentPageKey((string) $request->input('page_key', ''));
            if (! $pageKey) {
                return $this->withDeviceCookie(response()->json(['ok' => false], 422), $deviceToken, $request);
            }
            return $this->heartbeat($request, $user->getAuthIdentifier(), $deviceToken, $pageKey);
        }

        if ($routeName === 'parent.page-lock.release') {
            $pageKey = $this->parentPageKey((string) $request->input('page_key', ''));
            if ($pageKey) {
                $this->release($user->getAuthIdentifier(), $deviceToken, $pageKey);
            }
            return $this->withDeviceCookie(response()->json(['ok' => true]), $deviceToken, $request);
        }

        $pageKey = $this->pageKeyForRoute($routeName);
        $activePage = $request->cookie(self::ACTIVE_PAGE_COOKIE);
        $userId = $user->getAuthIdentifier();

        // A normal navigation to another page releases this device's previous
        // page lock. Refreshing the same page does not release it.
        if (is_string($activePage) && $activePage !== '' && $activePage !== $pageKey) {
            $this->release($userId, $deviceToken, $activePage);
        }

        if ($pageKey === null) {
            $response = $next($request);
            $response = $this->withDeviceCookie($response, $deviceToken, $request);
            return $response->withCookie(cookie()->forget(self::ACTIVE_PAGE_COOKIE));
        }

        $acquired = DB::transaction(function () use ($userId, $pageKey, $deviceToken) {
            $now = now();

            DB::table('user_page_locks')->insertOrIgnore([
                'user_id' => $userId,
                'page_key' => $pageKey,
                'lock_token' => $deviceToken,
                'last_seen_at' => $now,
                'expires_at' => $now->copy()->addSeconds(self::LEASE_SECONDS),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // This conditional UPDATE is atomic at the database level. Exactly
            // one competing device can change an active/expired row to its token.
            $updated = DB::table('user_page_locks')
                ->where('user_id', $userId)
                ->where('page_key', $pageKey)
                ->where(function ($query) use ($deviceToken, $now) {
                    $query->where('lock_token', $deviceToken)
                        ->orWhere('expires_at', '<=', $now);
                })
                ->update([
                    'lock_token' => $deviceToken,
                    'last_seen_at' => $now,
                    'expires_at' => $now->copy()->addSeconds(self::LEASE_SECONDS),
                    'updated_at' => $now,
                ]);

            return $updated === 1;
        }, 3);

        if (! $acquired) {
            $response = response()->view('errors.concurrent-access', [
                'retryUrl' => $request->fullUrl(),
            ], 409);
            $response = $this->withDeviceCookie($response, $deviceToken, $request);
            return $response->withCookie(cookie()->forget(self::ACTIVE_PAGE_COOKIE));
        }

        $response = $next($request);
        $response = $this->withDeviceCookie($response, $deviceToken, $request);
        return $response->withCookie(cookie(
            self::ACTIVE_PAGE_COOKIE,
            $pageKey,
            60 * 24 * 30,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax'
        ));
    }

    private function heartbeat(Request $request, int|string $userId, string $deviceToken, string $pageKey): Response
    {
        $updated = DB::table('user_page_locks')
            ->where('user_id', $userId)
            ->where('page_key', $pageKey)
            ->where('lock_token', $deviceToken)
            ->where('expires_at', '>', now())
            ->update([
                'last_seen_at' => now(),
                'expires_at' => now()->addSeconds(self::LEASE_SECONDS),
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return $this->withDeviceCookie(response()->json([
                'ok' => false,
                'message' => 'This page is active on another device or its lock has expired.',
            ], 409), $deviceToken, $request);
        }

        return $this->withDeviceCookie(response()->json(['ok' => true]), $deviceToken, $request);
    }

    private function release(int|string $userId, string $deviceToken, string $pageKey): void
    {
        // Never let a stale tab release a lock owned by another device.
        DB::table('user_page_locks')
            ->where('user_id', $userId)
            ->where('page_key', $pageKey)
            ->where('lock_token', $deviceToken)
            ->delete();
    }

    private function pageKeyForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        if (str_starts_with($routeName, 'teacher.activities.') && ! str_contains($routeName, '.lock.')) {
            return 'teacher-activities';
        }

        return match ($routeName) {
            'parent.dashboard', 'parent.analytics.index' => 'parent-dashboard',
            'parent.results.index' => 'parent-results',
            'parent.milestones.index' => 'parent-milestones',
            'parent.attendance.index' => 'parent-attendance',
            default => null,
        };
    }

    private function parentPageKey(string $routeName): ?string
    {
        return match ($routeName) {
            'parent.dashboard', 'parent.analytics.index' => 'parent-dashboard',
            'parent.results.index' => 'parent-results',
            'parent.milestones.index' => 'parent-milestones',
            'parent.attendance.index' => 'parent-attendance',
            default => null,
        };
    }

    private function withDeviceCookie(Response $response, string $deviceToken, Request $request): Response
    {
        return $response->withCookie(cookie(
            self::DEVICE_COOKIE,
            $deviceToken,
            60 * 24 * 365,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax'
        ));
    }
}
