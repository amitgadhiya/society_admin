<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile'   => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'mobile'   => $request->mobile,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['mobile' => 'Invalid mobile number or password.'])->withInput();
        }

        $user = Auth::user();

        if ($user->role === 'owner') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['mobile' => 'Owners are not allowed to log in here.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
