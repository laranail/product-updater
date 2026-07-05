# Commands

The self-update commands (`product:*`). See the [Documentation index](../../README.md#documentation).

| Command | Purpose |
|---------|---------|
| `product:update-check` | Query the update source and report whether a newer release is available. |
| `product:update` | Download, verify, and apply the latest release (refuses when unlicensed). |
| `product:doctor` | Diagnose the updater setup (source reachability, license, paths, permissions). |

```bash
php artisan product:update-check
php artisan product:update
php artisan product:doctor
```

`product:update` runs the full pipeline — download → verify (rejects `.env` + corrupt zips) → extract →
optional `migrate` + `publish` steps → cache clear — gated by a valid license via
`laranail/license-verifier`. See [Architecture](../architecture.md).

---

[← Docs index](../../README.md#documentation)
