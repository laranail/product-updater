# Getting started

Point the updater at your release source and apply an update.

## 1. Install + publish

```bash
composer require laranail/product-updater
php artisan vendor:publish --tag=product-updater-config
```

See [Installation](installation.md).

## 2. Configure the source

Set the source + product identity in `.env`:

```dotenv
PRODUCT_UPDATER_SOURCE=http          # http | envato
PRODUCT_UPDATER_URL=https://releases.example.com/api
PRODUCT_UPDATER_API_KEY=...
PRODUCT_UPDATER_PRODUCT_ID=my-product
PRODUCT_UPDATER_VERSION=1.2.0
```

Full key reference: [Configuration](configuration.md).

## 3. Check + update

```bash
php artisan product:update-check     # is a newer release available?
php artisan product:update           # download + verify + apply (refuses when unlicensed)
```

The update is gated by a valid license via `laranail/license-verifier`.

## Next steps

- [Configuration](configuration.md) — channels, steps (migrate/publish), backups, license gating.
- [Architecture](architecture.md) — how a self-update is checked, verified, and applied.
- [Commands](tools/commands.md) — the full command set.

---

[← Docs index](../README.md#documentation)
