# Architecture

How a self-update is checked, verified, and applied. See the [Documentation index](../README.md#documentation).

## The moving parts

- **`ProductUpdater`** (+ facade) — the orchestrator behind the `product:*` commands.
- **Sources** (`Sources/HttpUpdateSource`, `EnvatoUpdateSource`) — resolve the latest release for the
  configured `product_id` + `channel`.
- **License gate** — before applying, checks a valid license/entitlement via
  [`laranail/license-verifier`](https://github.com/laranail/license-verifier) (`require_license` /
  `require_entitlement`).
- **Download + verify** — fetches the archive to `paths.download`, rejecting `.env` files and corrupt zips.
- **Apply** — extracts into `paths.base`, then runs the optional `migrate` + `publish` steps and clears
  caches; `.env` is backed up first when `backup_env` is set.
- **Doctor** — diagnoses source reachability, license state, paths, and permissions.
- **Events + Listeners** — lifecycle hooks around check/download/apply.

## Flow

1. `product:update-check` asks the source for the latest release and compares it to `current_version`.
2. `product:update` verifies the license, downloads + validates the archive, extracts it, runs the
   configured steps, and clears caches — refusing to proceed when unlicensed.

---

[← Docs index](../README.md#documentation)
