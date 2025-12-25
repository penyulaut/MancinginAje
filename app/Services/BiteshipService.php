<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BiteshipService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $originPostalCode;

    public function __construct()
    {
        $this->apiKey = config('biteship.api_key') ?? '';
        $this->baseUrl = config('biteship.base_url') ?? '';
        $this->originPostalCode = config('biteship.origin_postal_code') ?? 0;
    }

    public function getRates(int $destinationId, string $courier): array
    {
        $response = Http::withOptions([
            'verify' => false, // penting di Windows / dev
        ])->withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . '/v1/rates/couriers', [
            'origin_postal_code'      => $this->originPostalCode,
            'destination_location_id' => $destinationId,
            'couriers'                => $courier,
            'items' => [
                [
                    'name'   => 'Produk Test',
                    'weight' => 1000,
                    'value'  => 100000,
                ],
            ],
        ]);

        return $response->json();
    }
}
