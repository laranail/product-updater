# laranail/product-updater

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/product-updater.svg)](https://packagist.org/packages/laranail/product-updater)
[![Tests](https://github.com/laranail/product-updater/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/product-updater/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/product-updater/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/product-updater/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

> License-gated self-update engine for Laravel products.

`laranail/product-updater` checks an update source, downloads and verifies release archives, and applies
them safely:

- **Sources** — a generic HTTP endpoint or Envato/CodeCanyon.
- **Safe apply** — rejects `.env` files + corrupt zips, backs up `.env`, extracts, runs migrations +
  asset publishing, clears caches.
- **License-gated** — refuses to update unless a valid license/entitlement is present via
  `laranail/license-verifier`.

Requires PHP `^8.4 || ^8.5`, Laravel `^13`, and `laranail/license-verifier`.

## Install

```bash
composer require laranail/product-updater
php artisan vendor:publish --tag=product-updater-config
```

See [Installation](docs/installation.md).

## Quick start

```bash
php artisan product:update-check     # is a newer release available?
php artisan product:update           # download + verify + apply (refuses when unlicensed)
```

Configure the source in `.env` first — see [Getting started](docs/getting-started.md).

## <a name="documentation"></a>Documentation

Hosted at [`opensource.simtabi.com/product-updater/docs/`](https://opensource.simtabi.com/product-updater/docs/).
The same pages live under [`docs/`](docs/):

### Guides

- [Installation](docs/installation.md) — install, requirements, publish.
- [Getting started](docs/getting-started.md) — configure a source and update.
- [Configuration](docs/configuration.md) — every config key.
- [Architecture](docs/architecture.md) — sources, verification, apply pipeline, license gate.

### Reference

- [Commands](docs/tools/commands.md) — `product:update` / `product:update-check` / `product:doctor`.

### Project

- [Changelog](CHANGELOG.md) — release history.

## Stability

Pre-1.0 (0.x) — the public API may change between minor versions. Pin a version before bumping.

## Local development

```bash
composer test
```

## Sister packages

- [`laranail/license-verifier`](https://github.com/laranail/license-verifier) — the license gate this consumes.
- [`laranail/license-kit`](https://github.com/laranail/license-kit) — the self-hosted licensing server.

Part of the [laranail licensing ecosystem](https://opensource.simtabi.com/license-verifier/).

## Community

- [Issues](https://github.com/laranail/product-updater/issues) — bugs + feature requests.

## Contributing & security

- [CONTRIBUTING.md](CONTRIBUTING.md) — workflow + coding standards.
- [SECURITY.md](SECURITY.md) — how to report a vulnerability.

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
