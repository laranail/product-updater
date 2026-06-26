# laranail/product-updater

[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE.md)

> License-gated self-update engine for Laravel products.

Checks an update source, downloads and verifies release archives (rejecting `.env` and corrupt
zips), extracts files, and clears caches — all gated by a valid license via `laranail/license-verifier`.

```bash
php artisan product:update          # download + apply (refuses when unlicensed)
php artisan product:update-check
```

## Install

```bash
composer require laranail/product-updater
```

Requires `laranail/license-verifier`. PHP `^8.4 || ^8.5`, Laravel `^13`.

## Development

```bash
composer test
```

Part of the [laranail licensing ecosystem](https://opensource.simtabi.com/license-verifier/).
