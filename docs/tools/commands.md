# Commands

Three Artisan commands drive the self-update pipeline — canonically named
`laranail::product-updater.*`, each with a short `product:*` alias.

| Command | Alias | Purpose |
|---------|-------|---------|
| `laranail::product-updater.check` | `product:update-check` | Query the update source and report whether a newer release is available. |
| `laranail::product-updater.update` | `product:update` | Download, verify, and apply the latest release (refuses when unlicensed). |
| `laranail::product-updater.doctor` | `product:update-doctor` | Diagnose the updater setup (source reachability, license, paths, permissions). |

```bash
php artisan product:update-check
php artisan product:update
php artisan product:update-doctor
```

## Options

| Command | Option | Effect |
|---------|--------|--------|
| `laranail::product-updater.check` | `--json` | Emit the check result as JSON (for scripts / dashboards). |
| `laranail::product-updater.update` | `--download-only` | Download and verify the archive without extracting it. |
| `laranail::product-updater.doctor` | `--json` | Emit the diagnosis as JSON. |

`product:update` runs the full pipeline — download → verify (rejects `.env` + corrupt zips) → extract →
optional `migrate` + `publish` steps → cache clear — gated by a valid license via
`laranail/license-verifier`. See [Architecture](../architecture.md).

---

[← Docs index](../../README.md#documentation)
