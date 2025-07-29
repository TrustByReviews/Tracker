<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Resend API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key for Resend. You can find this in your Resend dashboard.
    |
    */
    'api_key' => env('RESEND_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default From Address
    |--------------------------------------------------------------------------
    |
    | This is the default from address for emails sent via Resend.
    |
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@resend.dev'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | This is the webhook secret for Resend webhooks.
    |
    */
    'webhook_secret' => env('RESEND_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Domain Configuration
    |--------------------------------------------------------------------------
    |
    | This is the domain configuration for Resend.
    |
    */
    'domain' => [
        'name' => env('RESEND_DOMAIN'),
        'dns' => [
            'dkim' => env('RESEND_DKIM'),
            'spf' => env('RESEND_SPF'),
            'mx' => env('RESEND_MX'),
        ],
    ],
]; 