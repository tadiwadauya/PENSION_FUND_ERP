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
            'current_otp' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = auth()->user();

        if ($request->current_otp !== $user->otp_plain) {
            return back()->withErrors([
                'current_otp' => 'The one time password is incorrect.',
            ])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->otp_plain = null;
        $user->must_change_password = false;
        $user->password_changed_at = now();
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Password changed successfully.');
    }
}