<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BiteshipService;

class ShippingController extends Controller
{
    public function check(Request $request, BiteshipService $biteship)
    {
        $request->validate([
            'destination_id' => 'required|integer',
            'courier' => 'required|string',
        ]);

        $response = $biteship->getRates(
            $request->destination_id,
            $request->courier
        );

        if (!isset($response['success']) || $response['success'] === false) {
            return response()->json([
                'success' => false,
                'message' => $response['error'] ?? 'Ongkir tidak tersedia',
            ]);
        }

        return response()->json($response);
    }
}
