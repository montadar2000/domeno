<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $loggedIn = Auth::attempt([
            'username' => $request->string('username')->toString(),
            'password' => $request->string('password')->toString(),
        ], true);

        if (! $loggedIn) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'اسم المستخدم أو كلمة السر غلط.']);
        }

        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function createRegistration(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $username = $request->string('username')->toString();

        $user = User::query()->create([
            'name' => $username,
            'username' => $username,
            'password' => $request->string('password')->toString(),
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
