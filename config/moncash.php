<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    "version" => 'v1',


    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    "mode" => [
        "debug" => (bool) env("MONCASH_DEBUG_MODE", true),
        "app_debug" => env("APP_DEBUG", true),    
    ],


    /*
    |--------------------------------------------------------------------------
    | Identifier
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    "identifier" => [
        'client' => env('MONCASH_CLIENT_ID', false),
        'secret' => env('MONCASH_SECRET_KEY', false),
        'business_key' => env('MONCASH_BUSINESS_KEY', false),
    ],


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    "header" => [
        "content_type" => "application/json",
        "oauth_params" => [
            "scope"=> "read,write",
            "grant_type" => "client_credentials"
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    "endpoint" => [
        "base" => "moncashbutton.digicelgroup.com/Api",
        "redirect" => "moncashbutton.digicelgroup.com/Moncash-middleware",
    ],


    /*
    |--------------------------------------------------------------------------
    | Uri
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    "uri" => [
        "oauth" => "/oauth/token",
        "redirect" => "/Payment/Redirect?token=",
        "create_payment" => "/v1/CreatePayment",
        "retrieve_transaction" => "/v1/RetrieveTransactionPayment",
        "retrieve_order" => "/v1/RetrieveOrderPayment",
        "transfert" => "/v1/Transfert",
    ],


    /*
    |--------------------------------------------------------------------------
    | String
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    "string" => [
        "sandbox" => "sandbox",
        "live" => "live",
        "https" => "https://",
        
        "transaction_id" => "transactionId",
        "order_id" => "orderId",
    ],
];

