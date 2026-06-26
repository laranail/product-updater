<?php

declare(strict_types=1);

return [
    /*
    | The update source (HTTP server that lists/serves releases).
    */
    'source' => [
        'driver' => env('PRODUCT_UPDATER_SOURCE', 'http'),
        'url' => env('PRODUCT_UPDATER_URL'),
        'api_key' => env('PRODUCT_UPDATER_API_KEY'),
    ],

    /*
    | Product identity.
    */
    'product_id' => env('PRODUCT_UPDATER_PRODUCT_ID'),
    'current_version' => env('PRODUCT_UPDATER_VERSION', '1.0.0'),
    'channel' => env('PRODUCT_UPDATER_CHANNEL', 'stable'),
    'minimum_php_version' => env('PRODUCT_UPDATER_MIN_PHP'),

    /*
    | Require a valid license (via laranail/license-verifier) before any
    | download/update is allowed. May also require an "updates" entitlement.
    */
    'require_license' => env('PRODUCT_UPDATER_REQUIRE_LICENSE', true),
    'require_entitlement' => env('PRODUCT_UPDATER_REQUIRE_ENTITLEMENT'),

    /*
    | Filesystem paths.
    */
    'paths' => [
        'base' => base_path(),
        'download' => storage_path('app/updates'),
    ],

    /*
    | Download/HTTP behaviour.
    */
    'timeout' => env('PRODUCT_UPDATER_TIMEOUT', 300),
    'verify_tls' => env('PRODUCT_UPDATER_VERIFY_TLS', true),

    /*
    | Archive integrity. When the source publishes a SHA-256 for a release, the
    | downloaded file is verified before extraction. Set a PEM public key to also
    | verify a detached signature the source provides.
    */
    'verify_checksum' => env('PRODUCT_UPDATER_VERIFY_CHECKSUM', true),
    'public_key' => env('PRODUCT_UPDATER_PUBLIC_KEY'),

    // Transient-failure retry policy for the HTTP update source.
    'retries' => (int) env('PRODUCT_UPDATER_RETRIES', 2),
    'retry_delay' => (int) env('PRODUCT_UPDATER_RETRY_DELAY', 250),
];
