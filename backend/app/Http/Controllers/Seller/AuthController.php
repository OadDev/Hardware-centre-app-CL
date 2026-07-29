<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->hasRole('Seller')) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those credentials do not match our records.'])->onlyInput('email');
        }

        if (! Auth::user()->hasRole('Seller') || ! Auth::user()->seller) {
            Auth::logout();

            return back()->withErrors(['email' => 'This account is not registered as a seller.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('seller.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('seller.login');
    }
}
