<?php

return [
    'merchant_id' => trim(env('MIDTRANS_MERCHANT_ID', '')),
    'client_key' => trim(env('MIDTRANS_CLIENT_KEY', '')),
    'server_key' => trim(env('MIDTRANS_SERVER_KEY', '')),
    // Optional explicit mode: 'sandbox' or 'production'. When set, this takes precedence
    // over the boolean `MIDTRANS_IS_PRODUCTION`. If left null, the boolean flag will be used.
    'mode' => env('MIDTRANS_ENV', null),
    'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),
];
