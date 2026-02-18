<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | PhonePe Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'sandbox' for testing or 'production' for live transactions.
    |
    */
    'environment' => env('PHONEPE_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Your PhonePe merchant credentials from the dashboard.
    |
    */
    'credentials' => [
        'merchant_id' => env('PHONEPE_MERCHANT_ID'),
        'client_id' => env('PHONEPE_CLIENT_ID'),
        'client_secret' => env('PHONEPE_CLIENT_SECRET'),
        'client_version' => env('PHONEPE_CLIENT_VERSION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment URLs
    |--------------------------------------------------------------------------
    |
    | Base URLs and redirect URLs for sandbox and production environments.
    |
    */
    'urls' => [
        'sandbox' => [
            'base_url' => 'https://api-preprod.phonepe.com/apis',
            'redirect_success' => env('PHONEPE_SUCCESS_URL'),
            'redirect_failure' => env('PHONEPE_FAILURE_URL'),
        ],
        'production' => [
            'base_url' => 'https://api.phonepe.com/apis',
            'redirect_success' => env('PHONEPE_SUCCESS_URL'),
            'redirect_failure' => env('PHONEPE_FAILURE_URL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials for webhook signature verification.
    | Configure these in your PhonePe merchant dashboard.
    |
    */
    'webhook' => [
        'username' => env('PHONEPE_WEBHOOK_USERNAME'),
        'password' => env('PHONEPE_WEBHOOK_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Resilience Settings
    |--------------------------------------------------------------------------
    |
    | Configure retry policy and circuit breaker for production resilience.
    |
    */
    'resilience' => [
        // Retry Policy
        'retry' => [
            'enabled' => env('PHONEPE_RETRY_ENABLED', true),
            'max_attempts' => env('PHONEPE_RETRY_MAX_ATTEMPTS', 3),
            'base_delay_ms' => env('PHONEPE_RETRY_BASE_DELAY', 1000), // milliseconds
            'max_delay_ms' => env('PHONEPE_RETRY_MAX_DELAY', 10000), // milliseconds
            'jitter' => env('PHONEPE_RETRY_JITTER', true), // Add randomness to prevent thundering herd
        ],

        // Circuit Breaker
        'circuit_breaker' => [
            'enabled' => env('PHONEPE_CIRCUIT_BREAKER_ENABLED', true),
            'failure_threshold' => env('PHONEPE_CIRCUIT_BREAKER_THRESHOLD', 5),
            'success_threshold' => env('PHONEPE_CIRCUIT_BREAKER_SUCCESS_THRESHOLD', 2), // Successes needed to close from half-open
            'cooldown_seconds' => env('PHONEPE_CIRCUIT_BREAKER_COOLDOWN', 60),
        ],

        // Timeouts
        'timeout' => [
            'connect_seconds' => env('PHONEPE_CONNECT_TIMEOUT', 5),
            'request_seconds' => env('PHONEPE_REQUEST_TIMEOUT', 15),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Cache Settings
    |--------------------------------------------------------------------------
    |
    | OAuth token caching configuration.
    | PhonePe tokens typically expire at 3600 seconds (1 hour).
    |
    */
    'token' => [
        'cache_store' => env('PHONEPE_CACHE_STORE', null), // null = default cache
        'cache_key_prefix' => 'phonepe_oauth_token',
        'cache_ttl_buffer' => 90, // Refresh token 90 seconds before expiry
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Control logging behavior for PhonePe API interactions.
    |
    */
    'logging' => [
        'enabled' => env('PHONEPE_LOGGING', true),
        'channel' => env('PHONEPE_LOG_CHANNEL', null), // null = default log channel
        'debug' => env('PHONEPE_DEBUG', false), // Log full request/response bodies
        'log_requests' => env('PHONEPE_LOG_REQUESTS', true),
        'log_responses' => env('PHONEPE_LOG_RESPONSES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | API version to use. Currently supported: v1
    | Future versions can be added without breaking changes.
    |
    */
    'api_version' => env('PHONEPE_API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Adapter
    |--------------------------------------------------------------------------
    |
    | Choose HTTP client implementation: 'laravel' or 'guzzle'
    | Laravel adapter uses Illuminate\Http\Client
    | Guzzle adapter uses GuzzleHttp\Client
    |
    */
    'http_client' => env('PHONEPE_HTTP_CLIENT', 'laravel'),
];
