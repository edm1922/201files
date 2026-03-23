<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();
        
        // Auto-generate default password: {last_name}csc
        $defaultPassword = strtolower($validated['last_name']) . 'csc';
        $validated['password'] = Hash::make($defaultPassword);
        $validated['must_change_password'] = true;
        
        $user = User::create($validated);
        
        AuditService::log('created', "Created new user: {$user->username}", $user);

        return redirect()->route('settings.users.index')
                         ->with('success', 'User created successfully. Default password is: ' . $defaultPassword);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();
        
        $user->update($validated);

        AuditService::log('updated', "Updated user: {$user->username}", $user);

        return redirect()->route('settings.users.index')
                         ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('settings.users.index')
                             ->with('error', 'You cannot delete your own account.');
        }

        $username = $user->username;
        $user->delete();

        AuditService::log('deleted', "Deleted user: {$username}");
        return redirect()->route('settings.users.index')
                         ->with('success', 'User deleted successfully.');
    }

    /**
     * Reset the user's password to the default systematically.
     */
    public function resetPassword(User $user)
    {
        // Don't allow resetting own password to avoid lockouts during an active session
        if (Auth::id() === $user->id) {
            return redirect()->route('settings.users.index')
                             ->with('error', 'You cannot reset your own password here. Please use normal password change mechanisms.');
        }

        $defaultPassword = strtolower($user->last_name) . 'csc';
        
        $user->update([
            'password' => Hash::make($defaultPassword),
            'must_change_password' => true,
        ]);

        AuditService::log('updated', "Reset password for user: {$user->username}", $user);

        return redirect()->route('settings.users.index')
                         ->with('success', 'Password reset successfully for ' . $user->name . '. Their new password is: ' . $defaultPassword);
    }
}
