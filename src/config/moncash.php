<?php

return [

    "debug_mode" => env("MONCASH_DEBUG_MODE", false),


    'client' => env('MONCASH_CLIENT_ID', false),


    'secret' => env('MONCASH_SECRET_KEY', false),


    'business_key' => env('MONCASH_BUSINESS_KEY', false),


    "header_content_type" => "application/json",


    "oauth_params" => [
        "scope"=> "read,write",
        "grant_type" => "client_credentials"
    ],


    "sandbox_string" => "sandbox",


    "live_string" => "live",


    "https_string" => "https://",


    "base_endpoint" => "moncashbutton.digicelgroup.com/Api",


    "redirect_endpoint" => "moncashbutton.digicelgroup.com/Moncash-middleware",


    "redirect_uri" => "/Payment/Redirect?token=",


    "oauth_uri" => "/oauth/token",


    "create_payment_uri" => "/v1/CreatePayment",


    "retrieve_transaction_uri" => "/v1/RetrieveTransactionPayment",


    "retrieve_order_uri" => "/v1/RetrieveOrderPayment",


    "transfert_uri" => "/v1/Transfert",
];

