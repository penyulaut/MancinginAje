<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Biteship API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API key, base URL and defaults used by the Biteship service.
    |
    */

    'api_key' => env('BITESHIP_API_KEY', 'api_biteship_live_eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoibWFuY2luZ2luYWplIiwidXNlcklkIjoiNjk0MTYwODM5OGUxZGY0ZTUyNjAzMjcyIiwiaWF0IjoxNzY2MzIwMjk4fQ.fdA3fGdJuFsQryjt_klp1eS4Q59DqPPzM49f1fjmWNU'),

    'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com/v1'),

    // Default per-product weight in grams when product weight not available
    'default_weight_grams' => env('BITESHIP_DEFAULT_WEIGHT_GRAMS', 1000),

    // HTTP client timeout in seconds
    'timeout' => env('BITESHIP_TIMEOUT', 15),
];
