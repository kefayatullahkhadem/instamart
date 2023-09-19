<?php

return [

    'payment_3ds_return_domain' => env("PAYMENT_3DS_RETURN_DOMAIN", null),
    'default_currency_code' => env("DEFAULT_CURRENCY_CODE", null),
    'commercial_mpgs_mid' => env("COMMERCIAL_MPGS_MID", null),
    'token_server_url' => env("TOKEN_SERVER_URL", null),
    'token_server_username' => env("TOKEN_SERVER_USERNAME", null),
    'token_server_password' => env("TOKEN_SERVER_PASSWORD", null),
    'user_name' => env('API_USERNAME', NULL),
    'password' => env('API_PASSWORD', NULL),
    'webx_url' => env('WEBX_URL', NULL),
    'public_key' => env('PUBLIC_KEY', NULL),
    'secret_key' => env('SECRET_KEY', NULL),
];
