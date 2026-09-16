# Commands

Three Artisan commands drive the self-update pipeline, named
`laranail::product-updater.*`.

The short `product:*` aliases these once carried are gone. Artisan keeps command
names in a flat global map, so a bare `product:update` is a live collision with
any sibling package or host application that claims it — and the second claimant
silently replaces the first, which surfaces far away as the wrong code running
under a familiar name.

| Command | Purpose |
|---------|---------|
| `laranail::product-updater.check` | Query the update source and report whether a newer release is available. |
| `laranail::product-updater.update` | Download, verify, and apply the latest release (refuses when unlicensed). |
| `laranail::product-updater.doctor` | Diagnose the updater setup (source reachability, license, paths, permissions). |

```bash
php artisan laranail::product-updater.check
php artisan laranail::product-updater.update
php artisan laranail::product-updater.doctor
```

## Options

| Command | Option | Effect |
|---------|--------|--------|
| `laranail::product-updater.check` | `--json` | Emit the check result as JSON (for scripts / dashboards). |
| `laranail::product-updater.update` | `--download-only` | Download and verify the archive without extracting it. |
| `laranail::product-updater.doctor` | `--json` | Emit the diagnosis as JSON. |

`laranail::product-updater.update` runs the full pipeline — download → verify (rejects `.env` + corrupt zips) → extract →
optional `migrate` + `publish` steps → cache clear — gated by a valid license via
`laranail/license-verifier`. See [Architecture](../architecture.md).

---

[← Docs index](../../README.md#documentation)
