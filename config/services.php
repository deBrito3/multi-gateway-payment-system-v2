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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gateway_one' => [
        'url' => env('GATEWAY_ONE_URL', 'http://localhost:3001'),
        'email' => env('GATEWAY_ONE_EMAIL', 'dev@betalent.tech'),
        'token' => env('GATEWAY_ONE_TOKEN', 'FEC9BB078BF338F464F96B48089EB498'),
    ],

    'gateway_two' => [
        'url' => env('GATEWAY_TWO_URL', 'http://localhost:3002'),
        'auth_token' => env('GATEWAY_TWO_AUTH_TOKEN', 'tk_f2198cc671b5289fa856'),
        'auth_secret' => env('GATEWAY_TWO_AUTH_SECRET', '3d15e8ed6131446ea7e3456728b1211f'),
    ],

];
