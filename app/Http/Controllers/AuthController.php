<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80', 'email' => 'required|email|max:255|unique:users', 'password' => ['required', 'confirmed', Password::min(8)]]);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect('/dashboard');
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (!Auth::attempt($data, $request->boolean('remember')) || $request->user()->is_banned) {
            Auth::logout();
            return back()->withErrors(['email' => 'Неверные данные или аккаунт заблокирован.']);
        }
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect('/');
    }

    public function profile(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80', 'bio' => 'nullable|string|max:2000']);
        $request->user()->update($data);
        return back()->with('success', 'Профиль обновлён');
    }
}
