<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExpireStudentSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'student') {
            return $next($request);
        }

        $timeout = max(1, (int) config('kidlink.student_session_timeout', 15)) * 60;
        $lastActivity = (int) $request->session()->get('student_last_activity', time());

        if (time() - $lastActivity >= $timeout) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired due to inactivity. Please sign in again.',
                    'login_url' => route('login'),
                ], 401);
            }

            return redirect()->route('login')->with('session_expired', 'You were signed out because your account was inactive for 15 minutes. Please sign in again.');
        }

        $request->session()->put('student_last_activity', time());

        return $next($request);
    }
}
