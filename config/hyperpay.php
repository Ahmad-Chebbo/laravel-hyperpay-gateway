<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HyperPay Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for HyperPay payment gateway
    |
    */

    'environment' => env('HYPERPAY_ENVIRONMENT', 'test'), // 'test' or 'live'

    'test' => [
        'url' => env('HYPERPAY_TEST_URL', 'https://eu-test.oppwa.com'),
        'token' => env('HYPERPAY_TEST_TOKEN'),
        'webhook_key' => env('HYPERPAY_TEST_WEBHOOK_KEY'),
        'entities' => [
            'visa' => env('HYPERPAY_TEST_VISA_ENTITY_ID'),
            'master' => env('HYPERPAY_TEST_MASTER_ENTITY_ID'),
            'mada' => env('HYPERPAY_TEST_MADA_ENTITY_ID'),
            'applepay' => env('HYPERPAY_TEST_APPLEPAY_ENTITY_ID'),
            'stcpay' => env('HYPERPAY_TEST_STCPAY_ENTITY_ID'),
        ],
    ],

    'live' => [
        'url' => env('HYPERPAY_LIVE_URL', 'https://oppwa.com'),
        'token' => env('HYPERPAY_LIVE_TOKEN'),
        'webhook_key' => env('HYPERPAY_LIVE_WEBHOOK_KEY'),
        'entities' => [
            'visa' => env('HYPERPAY_LIVE_VISA_ENTITY_ID'),
            'master' => env('HYPERPAY_LIVE_MASTER_ENTITY_ID'),
            'mada' => env('HYPERPAY_LIVE_MADA_ENTITY_ID'),
            'applepay' => env('HYPERPAY_LIVE_APPLEPAY_ENTITY_ID'),
            'stcpay' => env('HYPERPAY_LIVE_STCPAY_ENTITY_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */

    'currency' => env('HYPERPAY_CURRENCY', 'SAR'),
    'payment_type' => env('HYPERPAY_PAYMENT_TYPE', 'DB'), // DB (debit) or PA (preauthorization)

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */

    'webhook' => [
        'enabled' => env('HYPERPAY_WEBHOOK_ENABLED', true),
        'url' => env('HYPERPAY_WEBHOOK_URL'),
        'verify_signature' => env('HYPERPAY_WEBHOOK_VERIFY_SIGNATURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('HYPERPAY_LOGGING_ENABLED', true),
        'channel' => env('HYPERPAY_LOGGING_CHANNEL', 'single'),
        'level' => env('HYPERPAY_LOGGING_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customization Options
    |--------------------------------------------------------------------------
    */

    'customization' => [
        'auto_redirect' => env('HYPERPAY_AUTO_REDIRECT', true),
        'retry_attempts' => env('HYPERPAY_RETRY_ATTEMPTS', 3),
        'timeout' => env('HYPERPAY_TIMEOUT', 30),
        'result_url' => env('HYPERPAY_RESULT_URL'),
        'cancel_url' => env('HYPERPAY_CANCEL_URL'),
        'error_url' => env('HYPERPAY_ERROR_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Payment Brands
    |--------------------------------------------------------------------------
    */

    'supported_brands' => [
        'VISA',
        'MASTER',
        'MADA',
        'APPLEPAY',
        'STCPAY',
    ],

    /*
    |--------------------------------------------------------------------------
    | Risk Management
    |--------------------------------------------------------------------------
    */

    'risk_management' => [
        'enabled' => env('HYPERPAY_RISK_MANAGEMENT_ENABLED', true),
        'max_amount' => env('HYPERPAY_MAX_AMOUNT'),
        'min_amount' => env('HYPERPAY_MIN_AMOUNT', 1),
        'blocked_countries' => env('HYPERPAY_BLOCKED_COUNTRIES', ''),
        'allowed_countries' => env('HYPERPAY_ALLOWED_COUNTRIES', ''),
    ],
];
