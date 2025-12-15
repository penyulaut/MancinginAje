<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function submitRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:customer,seller',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        Auth::login($user);

        // Simpan user info di session untuk performa
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
        ]);

        return redirect()->route('pages.beranda')->with('success', 'Registrasi berhasil!');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }   

    public function submitLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // Simpan user info di session untuk performa
            $user = Auth::user();
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
            ]);
            
            // Regenerate CSRF token only, keep session ID
            $request->session()->regenerateToken();
            
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
