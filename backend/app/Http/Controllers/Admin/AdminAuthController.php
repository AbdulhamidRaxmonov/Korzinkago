<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('phone', preg_replace('/\D/', '', $data['phone']))
            ->where('role', 'admin')
            ->first();

        if (! $user || ! \Illuminate\Support\Facades\Hash::check($data['password'], (string) $user->password)) {
            return back()->withErrors(['phone' => 'Telefon yoki parol noto\'g\'ri.'])->onlyInput('phone');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
