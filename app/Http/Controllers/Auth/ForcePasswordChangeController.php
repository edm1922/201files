<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeController extends Controller
{
    /**
     * Show the force password change view.
     */
    public function show()
    {
        return view('auth.force-password-change');
    }

    /**
     * Handle an incoming password change request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', 'min:8', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user = $request->user();
        
        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        return redirect()->intended(route('dashboard', absolute: false))
                         ->with('success', 'Your password has been changed successfully.');
    }
}
