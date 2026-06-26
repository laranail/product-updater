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
];
