<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $orders = Orders::where('user_id', $user->id)->latest()->limit(5)->get();

        // Load user addresses and provinces for structured address form
        $addresses = $user->addresses()->orderByDesc('is_default')->get();

        // fetch provinces list from RajaOngkir to populate dropdown
        try {
            $provincesResp = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept' => 'application/json',
                'key' => config('rajaongkir.api_key'),
            ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');
            $provinces = $provincesResp->successful() ? $provincesResp->json()['data'] ?? [] : [];
        } catch (\Throwable $e) {
            $provinces = [];
        }

        return view('pages.profile', compact('user', 'orders', 'addresses', 'provinces'));
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
        // If the request includes structured address fields, create a new address record
        if ($request->has('province_id') || $request->has('label')) {
            $validated = $request->validate([
                'label' => 'nullable|string|max:255',
                'address_line' => 'required|string|max:1000',
                'province_id' => 'nullable|integer',
                'province_name' => 'nullable|string|max:255',
                'city_id' => 'nullable|integer',
                'city_name' => 'nullable|string|max:255',
                'district_id' => 'nullable|integer',
                'district_name' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
                'is_default' => 'nullable|boolean',
            ]);

            // if user wants this as default, clear previous default
            if (!empty($validated['is_default'])) {
                \App\Models\UserAddress::where('user_id', $user->id)->update(['is_default' => DB::raw('FALSE')]);
            }

            $addr = \App\Models\UserAddress::create([
                'user_id' => $user->id,
                'label' => $validated['label'] ?? null,
                'address_line' => $validated['address_line'],
                'province_id' => $validated['province_id'] ?? null,
                'province_name' => $validated['province_name'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'city_name' => $validated['city_name'] ?? null,
                'district_id' => $validated['district_id'] ?? null,
                'district_name' => $validated['district_name'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'is_default' => !empty($validated['is_default']) ? DB::raw('TRUE') : DB::raw('FALSE'),
            ]);

            return back()->with('success', 'Alamat baru ditambahkan.');
        }

        // fallback: simple address update on user table (legacy)
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

    public function deleteAddress(Request $request, $id)
    {
        $user = $request->user();
        $addr = \App\Models\UserAddress::where('id', $id)->where('user_id', $user->id)->first();
        if (! $addr) {
            return back()->with('error', 'Alamat tidak ditemukan.');
        }

        $addr->delete();
        return back()->with('success', 'Alamat berhasil dihapus.');
    }
}
