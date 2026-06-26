# Changelog

All notable changes to `laranail/product-updater` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial release.
- Self-update engine for licensed Laravel products: checks an update source,
  downloads and verifies release archives, extracts files, runs migrations and
  publishes assets.
- License gating via `laranail/license-verifier` (`require_license` +
  optional `require_entitlement`), passing the stored license token to the
  download endpoint.
