# laranail/product-updater

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/product-updater.svg)](https://packagist.org/packages/laranail/product-updater)
[![Tests](https://github.com/laranail/product-updater/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/product-updater/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/product-updater/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/product-updater/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

> License-gated self-update engine for Laravel products — checks an update source (a generic HTTP endpoint or Envato/CodeCanyon), downloads and verifies release archives, then applies them safely (rejects `.env`/corrupt zips, backs up `.env`, runs migrations + asset publishing, clears caches). Refuses to update without a valid license.

Requires PHP `^8.4 || ^8.5`, Laravel `^13`, and [`laranail/license-verifier`](https://opensource.simtabi.com/documentation/laranail/license-verifier/).

## Install

```bash
composer require laranail/product-updater
php artisan vendor:publish --tag=product-updater-config
```

The service provider and the `ProductUpdater` facade are auto-discovered.

## Quick start

Point the updater at your release source and product identity in `.env`:

```dotenv
PRODUCT_UPDATER_SOURCE=http          # http | envato
PRODUCT_UPDATER_URL=https://releases.example.com/api
PRODUCT_UPDATER_API_KEY=...
PRODUCT_UPDATER_PRODUCT_ID=my-product
PRODUCT_UPDATER_VERSION=1.2.0
```

Then check and update:

```bash
php artisan product:update-check     # is a newer release available?
php artisan product:update           # download + verify + apply (refuses when unlicensed)
php artisan product:update-doctor    # diagnose source, license, paths, permissions
```

The canonical command names are `laranail::product-updater.check` / `.update` / `.doctor`;
the `product:*` names above are their short aliases.

## The pipeline

`product:update` runs one guarded pipeline: license gate (via `laranail/license-verifier`) →
download to `paths.download` → verify (rejects `.env` files and corrupt zips) → back up `.env` →
extract into `paths.base` → optional `migrate` + `publish` steps → cache clear. Every stage fires
lifecycle events. See [Architecture](docs/architecture.md).

## <a name="documentation"></a>Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/product-updater](https://opensource.simtabi.com/documentation/laranail/product-updater/)** — getting started, update sources, the safe-apply pipeline, license gating, and configuration.

### Guides

- [Installation](docs/installation.md) — requirements, install, publishing the config.
- [Getting started](docs/getting-started.md) — configure a source and run your first update.
- [Configuration](docs/configuration.md) — every key in `config/product-updater.php`.
- [Architecture](docs/architecture.md) — how a self-update is checked, verified, and applied.
- [Release](docs/release.md) — versioning and how releases are cut.

### Reference

- [Commands](docs/tools/commands.md) — `laranail::product-updater.*` and the `product:*` aliases.

## Stability

Semver. The package is on the `0.x` line — minor versions may contain breaking changes until 1.0;
pin to a patch range and read the [CHANGELOG](CHANGELOG.md) before upgrading.

## Local development

```bash
composer install
composer test    # pest
composer lint    # pint + phpstan + rector --dry-run
```

## Sister packages

The laranail licensing family:

| Package | What it is |
|---|---|
| [`laranail/license-kit`](https://opensource.simtabi.com/documentation/laranail/license-kit/) | Server-side licensing — activation keys, polymorphic assignment, expirations/renewals, seat control. |
| [`laranail/license-verifier`](https://opensource.simtabi.com/documentation/laranail/license-verifier/) | Headless, provider-agnostic verification client — PASETO/Ed25519 offline verification, fingerprinting, seats, grace periods. |
| [`laranail/license-verifier-ui`](https://opensource.simtabi.com/documentation/laranail/license-verifier-ui/) | UI engine for the verifier — scaffolds owned, themeable preset packages (Blade, Livewire, Filament, Vue). |
| [`laranail/demo-mode`](https://opensource.simtabi.com/documentation/laranail/demo-mode/) | License-aware demo / sandbox controller — write guards, feature gating, resets, visitor isolation. |

## Community

Questions, bug reports, and ideas go to [GitHub Issues](https://github.com/laranail/product-updater/issues).

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (opensource@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
