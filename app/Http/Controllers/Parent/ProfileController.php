<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('parent.profile-edit', ['user' => auth()->user()]);
    }


    public function updateInitialPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        abort_unless($user->role === 'parent', 403);

        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->save();

        return response()->json(['ok' => true, 'message' => 'Your password has been updated.']);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'contact' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->contact = $validated['contact'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('parent.profile.edit')->with('success', 'Your parent profile has been updated.');
    }
}
