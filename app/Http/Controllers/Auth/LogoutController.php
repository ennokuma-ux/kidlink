<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        $parentId = $request->session()->get('parent_user_id');
        $parentRole = $request->session()->get('parent_role');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($parentId && $parentRole) {
            $intended = $parentRole === 'teacher' ? route('teacher.dashboard') : route('parent.dashboard');
            return redirect()->guest($intended);
        }

        return redirect()->route('home');
    }
}
