<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->isDeliveryAgent()) {
            return redirect()->route('delivery.dashboard');
        }

        return view('delivery.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== UserRole::DeliveryAgent || ! $user->is_active) {
            Auth::guard('web')->logout();

            return back()->withErrors(['email' => 'Access denied. Delivery partner only.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('delivery.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('delivery.login');
    }
}
