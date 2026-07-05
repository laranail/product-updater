# Installation

Install `laranail/product-updater` and publish its config. See the [Documentation index](../README.md#documentation).

## Requirements

- PHP `^8.4 || ^8.5`, Laravel `^13`
- [`laranail/license-verifier`](https://github.com/laranail/license-verifier) (the update is license-gated)

## Install

```bash
composer require laranail/product-updater
php artisan vendor:publish --tag=product-updater-config
```

The service provider + `ProductUpdater` facade are auto-discovered.

## Next steps

- [Getting started](getting-started.md) — configure a source and run your first update.
- [Configuration](configuration.md) — every config key.
- [Commands](tools/commands.md) — `product:update` / `product:update-check` / `product:doctor`.

---

[← Docs index](../README.md#documentation)
