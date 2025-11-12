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
        $data = new User();
        $data->name = $request->name;
        $data->email = $request->email;
        $data->password = bcrypt($request->password);
        $data->save();

        return redirect('/login')->with('success', 'Registration successful. Please login.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }   

    public function submitLogin(Request $request)
    {
        $data = User::where('name', $request->name)->first();

        if($data && \Hash::check($request->password, $data->password)){
            Auth::login($data);
            $request->session()->regenerate();
            return redirect('/beranda')->with('success', 'Login successful');
        }

        return redirect('/login')->with('error', 'Invalid credentials');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/beranda')->with('success', 'Logged out successfully');
    }
}
