<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'password' => ['required', 'confirmed', 'min:8', Password::defaults()],
        ]);

        $user = $request->user();
        $before = [
            'must_change_password' => (bool) $user->must_change_password,
        ];

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        AuditService::log('updated', 'Completed forced password change.', $user, [
            'before' => $before,
            'after' => [
                'must_change_password' => false,
            ],
        ]);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Your password has been changed successfully.');
    }
}
