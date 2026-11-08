<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('teacher.settings', compact('user'));
    }

    /**
     * Name and email stay admin-managed; a teacher can keep their own contact
     * number up to date.
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'contact' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update(['contact' => $validated['contact'] ?? null]);

        return redirect()->route('teacher.settings.index')->with('success', 'Contact updated!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return redirect()->route('teacher.settings.index')->with('success', 'Password changed!');
    }
}
