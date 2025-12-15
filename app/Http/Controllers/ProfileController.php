<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Orders;
use App\Models\User;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $orders = Orders::where('user_id', $user->id)->latest()->limit(5)->get();
        return view('pages.profile', compact('user', 'orders'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateAddress(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'address' => 'nullable|string|max:1000',
        ]);

        $user->update(['address' => $validated['address'] ?? null]);

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $orders = Orders::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);
        return view('pages.profile-history', compact('orders'));
    }
}
