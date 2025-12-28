<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function submitRegister(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'customer',
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        Auth::login($user);

        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
        ]);

        if ($user->role === 'seller') {
            return redirect()->route('dashboard.index')->with('success', 'Registrasi berhasil! Selamat datang, Seller.');
        }

        return redirect()->route('pages.beranda')->with('success', 'Registrasi berhasil!');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }   

    public function submitLogin(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
            ]);

            $request->session()->regenerateToken();

            if ($user->role === 'seller') {
                return redirect()->intended(route('dashboard.index'));
            }

            return redirect()->intended(route('pages.beranda'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        // Clear user session data
        $request->session()->forget(['user_id', 'user_name', 'user_email', 'user_role']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('success', 'Berhasil logout!');
    }
}
