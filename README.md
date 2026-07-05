# laranail/product-updater

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/product-updater.svg)](https://packagist.org/packages/laranail/product-updater)
[![Tests](https://github.com/laranail/product-updater/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/product-updater/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/product-updater/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/product-updater/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

> License-gated self-update engine for Laravel products — checks an update source (a generic HTTP endpoint or Envato/CodeCanyon), downloads and verifies release archives, then applies them safely (rejects `.env`/corrupt zips, backs up `.env`, runs migrations + asset publishing, clears caches). Refuses to update without a valid license.

Requires PHP `^8.4 || ^8.5`, Laravel `^13`, and `laranail/license-verifier`.

## Install

```bash
composer require laranail/product-updater
```

## Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/product-updater](https://opensource.simtabi.com/documentation/laranail/product-updater/)** — getting started, update sources, the safe-apply pipeline, license gating, and configuration.

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (opensource@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
