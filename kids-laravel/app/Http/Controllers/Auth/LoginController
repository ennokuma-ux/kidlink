<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => 'This account has been archived. Please contact your administrator.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            session([
                'user_id' => $user->id,
                'role' => $user->role,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->full_name,
            ]);

            if ($user->role === 'parent') {
                $child = $user->children()->where('is_active', true)->first();
                if ($child) {
                    session([
                        'child_id' => $child->id,
                        'child_name' => $child->name,
                    ]);
                }
            }

            return match ($user->role) {
                'super_admin' => redirect()->intended(route('admin.users.index')),
                'teacher' => redirect()->intended(route('teacher.dashboard')),
                'parent' => redirect()->intended(route('parent.dashboard')),
                'student' => redirect()->intended(route('student.home')),
                default => redirect()->intended(route('dashboard')),
            };
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
