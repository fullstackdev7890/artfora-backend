<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'twilio' => [
        'account_sid' => getenv('TWILIO_ACCOUNT_SID'),
        'auth_token' => getenv('TWILIO_AUTH_TOKEN'),
        'verification_sid' => getenv('TWILIO_VERIFICATION_SID')
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'webhook_secret_order' => env('STRIPE_WEBHOOK_SECRET_ORDER'),
        'stripe_currency' => env('STRIPE_CURRENCY', 'EUR'),
    ],

    'mtcaptcha' => [
        'secret' => env('MTCAPTCHA_PRIVATE_KEY')
    ]
];
