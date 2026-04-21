<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeController extends Controller
{
    /**
     * Show password change form.
     */
    public function edit()
    {
        return view('auth.force-change-password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request)
{
    $request->validate([
        'current_password' => ['required'],
        'password' => ['required', 'confirmed', 'min:8'],
    ]);

    $user = auth()->user();

    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors([
            'current_password' => 'Current password is incorrect.'
        ]);
    }

    $user->update([
        'password' => Hash::make($request->password),
        'must_change_password' => false,
    ]);

    return redirect()->route('dashboard')
        ->with('success', 'Password changed successfully.');
}
}