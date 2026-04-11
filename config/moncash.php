<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sandbox mode
    |--------------------------------------------------------------------------
    |
    | When true, requests hit the MonCash sandbox environment. Set to false
    | in production to hit the live MonCash gateway.
    |
    */

    'sandbox' => (bool) env('MONCASH_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | Your MonCash business application credentials. You can find these on
    | your MonCash business dashboard. `business_key` is optional and only
    | required for certain endpoints.
    |
    */

    'credentials' => [
        'client_id'    => env('MONCASH_CLIENT_ID'),
        'secret_key'   => env('MONCASH_SECRET_KEY'),
        'business_key' => env('MONCASH_BUSINESS_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    |
    | Fine-grained control over the outbound HTTP calls. `timeout` is the
    | maximum number of seconds to wait for a response; `retries` is the
    | number of times a failed (network-level) request should be retried.
    |
    */

    'http' => [
        'timeout'    => (int) env('MONCASH_HTTP_TIMEOUT', 15),
        'retries'    => (int) env('MONCASH_HTTP_RETRIES', 2),
        'retry_wait' => (int) env('MONCASH_HTTP_RETRY_WAIT', 200), // ms
    ],

    /*
    |--------------------------------------------------------------------------
    | Token cache
    |--------------------------------------------------------------------------
    |
    | MonCash OAuth access tokens are valid for a short window. We cache the
    | token to avoid re-authenticating on every single API call. Set `store`
    | to null to use the default cache store.
    |
    */

    'cache' => [
        'store'      => env('MONCASH_CACHE_STORE'),
        'key'        => 'moncash.access_token',
        'ttl_buffer' => 30, // subtract this many seconds from the reported TTL
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoints
    |--------------------------------------------------------------------------
    |
    | Hosts and paths for the MonCash gateway. You should rarely need to
    | change these unless MonCash updates their API.
    |
    */

    'endpoints' => [
        'live' => [
            'api'      => 'https://moncashbutton.digicelgroup.com/Api',
            'redirect' => 'https://moncashbutton.digicelgroup.com/Moncash-middleware',
        ],
        'sandbox' => [
            'api'      => 'https://sandbox.moncashbutton.digicelgroup.com/Api',
            'redirect' => 'https://sandbox.moncashbutton.digicelgroup.com/Moncash-middleware',
        ],
    ],

    'paths' => [
        'oauth'                => '/oauth/token',
        'create_payment'       => '/v1/CreatePayment',
        'retrieve_transaction' => '/v1/RetrieveTransactionPayment',
        'retrieve_order'       => '/v1/RetrieveOrderPayment',
        'transfer'             => '/v1/Transfert',
        'redirect'             => '/Payment/Redirect?token=',
    ],
];
