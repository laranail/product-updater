# Changelog

All notable changes to `laranail/product-updater` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v0.1.0](https://github.com/laranail/product-updater/releases/tag/v0.1.0/compare/v0.1.0...v0.1.0) - 2026-07-06

Initial release.

### Added

- **Self-update engine** for licensed Laravel products: checks an update
  source, downloads and verifies release archives, extracts files, runs
  migrations, publishes assets, and clears caches — behind a cache-based
  update lock.
- **Update sources** behind the `UpdateSource` contract
  (`checkConnection()`, `check()`, `getUpdateSize()`, `download()`):
  - **HTTP source** (`source.driver = http`, Botble-style) — POSTs
    `check_update` and streams the release archive; parses `checksum`,
    `signature`, and `min_php` from release payloads.
  - **Envato / license-bridge source** (`source.driver = envato`,
    Botble-style) — `check_connection_ext` / `check_update` /
    `get_update_size` / `download_update/main/{id}` with `LB-*` headers
    (API key, install URL, client IP, language).
  
- **Safe-apply pipeline** — rejects archives that bundle a `.env` or fail
  zip validation, backs up (and can restore) the host `.env`, and enforces
  each release's minimum PHP version before applying.
- **License gating** via `laranail/license-verifier` (`require_license` +
  optional `require_entitlement`), passing the stored license token to the
  download endpoint.
- **Artisan commands** — `product:update-check`, `product:update`, and
  `product:doctor` (source reachability, license, paths, permissions), on
  the `laranail::product-updater.*` namespaced base.
- **Events** for every pipeline stage — checking/checked,
  available/unavailable, downloading/downloaded, extraction, migrations,
  publishing, cache clearing, `.env` backup/restore, integrity and
  requirements failures.
- **Optional HTTP health endpoint** (`api.enabled`) reporting updater status.
- **Configuration** (`config/product-updater.php`) — source driver and
  credentials, product/version/channel identity, pipeline steps, backup
  policy, lock TTL, API exposure, and license sync.

## [0.1.0](https://github.com/laranail/product-updater/releases/tag/v0.1.0) - 2026-07-06

Initial release.

### Added

- **Self-update engine** for licensed Laravel products: checks an update
  source, downloads and verifies release archives, extracts files, runs
  migrations, publishes assets, and clears caches — behind a cache-based
  update lock.
- **Update sources** behind the `UpdateSource` contract
  (`checkConnection()`, `check()`, `getUpdateSize()`, `download()`):
  - **HTTP source** (`source.driver = http`, Botble-style) — POSTs
    `check_update` and streams the release archive; parses `checksum`,
    `signature`, and `min_php` from release payloads.
  - **Envato / license-bridge source** (`source.driver = envato`,
    Botble-style) — `check_connection_ext` / `check_update` /
    `get_update_size` / `download_update/main/{id}` with `LB-*` headers
    (API key, install URL, client IP, language).
  
- **Safe-apply pipeline** — rejects archives that bundle a `.env` or fail
  zip validation, backs up (and can restore) the host `.env`, and enforces
  each release's minimum PHP version before applying.
- **License gating** via `laranail/license-verifier` (`require_license` +
  optional `require_entitlement`), passing the stored license token to the
  download endpoint.
- **Artisan commands** — `product:update-check`, `product:update`, and
  `product:doctor` (source reachability, license, paths, permissions), on
  the `laranail::product-updater.*` namespaced base.
- **Events** for every pipeline stage — checking/checked,
  available/unavailable, downloading/downloaded, extraction, migrations,
  publishing, cache clearing, `.env` backup/restore, integrity and
  requirements failures.
- **Optional HTTP health endpoint** (`api.enabled`) reporting updater status.
- **Configuration** (`config/product-updater.php`) — source driver and
  credentials, product/version/channel identity, pipeline steps, backup
  policy, lock TTL, API exposure, and license sync.
