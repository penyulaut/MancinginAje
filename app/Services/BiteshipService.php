<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BiteshipService
{
    protected $client;
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'api_key' => config('biteship.api_key'),
            'base_url' => config('biteship.base_url'),
            'timeout' => config('biteship.timeout', 15),
            'default_weight_grams' => config('biteship.default_weight_grams', 1000),
        ], $config ?: []);

        $this->client = new Client([
            'base_uri' => rtrim($this->config['base_url'], '/') . '/',
            'timeout' => $this->config['timeout'],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config['api_key'],
            ],
        ]);
    }

    protected function request(string $method, string $path, array $options = [])
    {
        try {
            $response = $this->client->request($method, ltrim($path, '/'), $options);
            $body = (string) $response->getBody();
            return json_decode($body, true) ?? $body;
        } catch (RequestException $e) {
            $resp = $e->getResponse();
            if ($resp) {
                $body = (string) $resp->getBody();
                return json_decode($body, true) ?? ['error' => $body, 'status' => $resp->getStatusCode()];
            }

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Request rate/quote from Biteship.
     * Payload should contain origin, destination, weight (grams) and optionally courier/service
     */
    public function getRates(array $payload)
    {
        // Many Biteship APIs expose /rates or /services — use /rates as sensible default
        return $this->request('POST', 'rates', [
            'json' => $payload,
        ]);
    }

    /**
     * Create a shipment/order in Biteship.
     * Payload must follow Biteship expected fields (recipient, items, origin, service, etc.)
     */
    public function createShipment(array $payload)
    {
        return $this->request('POST', 'shipments', [
            'json' => $payload,
        ]);
    }

    /**
     * Track a shipment by AWB/tracking number (or fetch shipment by id)
     */
    public function track(string $awb)
    {
        return $this->request('GET', "shipments/{$awb}");
    }

    /**
     * Helper to compute weight for a product/quantity.
     */
    public function computeWeightGrams(int $quantity, ?int $perItemGrams = null): int
    {
        $per = $perItemGrams ?: $this->config['default_weight_grams'];
        return max(1, (int) $quantity) * (int) $per;
    }
}
