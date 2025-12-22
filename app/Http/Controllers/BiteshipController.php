<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BiteshipService;
use App\Models\Orders;
use App\Models\Order_items;

class BiteshipController extends Controller
{
    protected $biteship;

    public function __construct(BiteshipService $biteship)
    {
        $this->biteship = $biteship;
    }

    // POST /biteship/quote
    public function quote(Request $request)
    {
        $data = $request->validate([
            'origin' => 'required|array',
            'destination' => 'required|array',
            'weight' => 'nullable|numeric',
        ]);

        $payload = [
            'origin' => $data['origin'],
            'destination' => $data['destination'],
            'weight' => isset($data['weight']) ? (int)$data['weight'] : config('biteship.default_weight_grams'),
        ];

        $res = $this->biteship->getRates($payload);
        return response()->json($res);
    }

    // POST /biteship/create-shipment
    public function createShipment(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'service' => 'nullable|string',
        ]);

        $order = Orders::with('orderItems')->find($data['order_id']);
        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $items = [];
        $totalWeight = 0;
        foreach ($order->orderItems as $oi) {
            $qty = $oi->quantity ?? 1;
            $per = config('biteship.default_weight_grams');
            $w = $this->biteship->computeWeightGrams($qty, $per);
            $totalWeight += $w;

            $items[] = [
                'name' => $oi->product->name ?? ('product-'.$oi->product_id),
                'quantity' => $qty,
                'price' => $oi->price ?? 0,
                'weight' => $w,
            ];
        }

        // Build shipment payload using common fields Biteship expects.
        // Adjust keys as needed if your Biteship plan uses different schema.
        $payload = [
            'order_id' => $order->id,
            'recipient' => [
                'name' => $order->user->name ?? 'Pembeli',
                'phone' => $order->user->phone ?? null,
                'address' => $order->shipping_address ?? null,
                'city' => $order->shipping_city ?? null,
                'province' => $order->shipping_province ?? null,
                'postal_code' => $order->shipping_postal_code ?? null,
            ],
            'items' => $items,
            'weight' => $totalWeight,
            'service' => $data['service'] ?? null,
        ];

        $res = $this->biteship->createShipment($payload);

        // Optionally persist response data (tracking) to order here.
        if (is_array($res) && isset($res['data'])) {
            $order->update(['snap_token' => $order->snap_token ?? null, 'biteship' => json_encode($res['data'])]);
        }

        return response()->json($res);
    }

    // GET /biteship/track/{awb}
    public function track($awb)
    {
        $res = $this->biteship->track($awb);
        return response()->json($res);
    }
}
