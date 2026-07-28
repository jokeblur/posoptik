<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'generic'),
        'gateway_url' => env('WHATSAPP_GATEWAY_URL'),
        'gateway_token' => env('WHATSAPP_GATEWAY_TOKEN'),
        'wablas_url' => env('WHATSAPP_WABLAS_URL', 'https://sby.wablas.com/api/send-message'),
        'wablas_token' => env('WHATSAPP_WABLAS_TOKEN'),
        'wablas_secret' => env('WHATSAPP_WABLAS_SECRET'),
        'callmebot_url' => env('WHATSAPP_CALLMEBOT_URL', 'https://api.callmebot.com/whatsapp.php'),
        'callmebot_apikey' => env('WHATSAPP_CALLMEBOT_APIKEY'),
    ],

];
