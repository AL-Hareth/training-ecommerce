<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create() {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request) {
        $validatedData = $request->validated();
        if (!Auth::attempt($validatedData)) {
            return redirect()->route('login')->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        // Regenerate the session ID to prevent fixation and ensure fresh state
        $request->session()->regenerate();

        // Force a full reload so the Navbar immediately reflects the new auth state
        return redirect()->route('home');
    }

    public function destroy(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Force a full reload so the Navbar immediately reflects the logged-out state
        return Inertia::location(route('home'));
    }
}
