<?php

return [
    'environment' => env('MIDTRANS_ENV', 'sandbox'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOL),
    'snap_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions',
    'api_url' => 'https://api.sandbox.midtrans.com/v2',
    'timeout' => 15,
];
