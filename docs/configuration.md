# Configuration

Every key in `config/product-updater.php`. See the [Documentation index](../README.md#documentation).

## Keys

| Key | Env | Purpose |
|-----|-----|---------|
| `source.driver` | `PRODUCT_UPDATER_SOURCE` | Update source: `http` (default) or `envato`. |
| `source.url` | `PRODUCT_UPDATER_URL` | The release/API endpoint. |
| `source.api_key` | `PRODUCT_UPDATER_API_KEY` | Credential for the source. |
| `product_id` | `PRODUCT_UPDATER_PRODUCT_ID` | Identifies your product to the source. |
| `current_version` | `PRODUCT_UPDATER_VERSION` | The installed version (compared against the source). |
| `channel` | `PRODUCT_UPDATER_CHANNEL` | Release channel (default `stable`). |
| `minimum_php_version` | `PRODUCT_UPDATER_MIN_PHP` | Refuse updates below this PHP version. |
| `require_license` | `PRODUCT_UPDATER_REQUIRE_LICENSE` | Gate updates behind a valid license (default `true`). |
| `require_entitlement` | `PRODUCT_UPDATER_REQUIRE_ENTITLEMENT` | Require a specific entitlement. |
| `paths.base` | — | Where files are applied (default `base_path()`). |
| `paths.download` | — | Where archives are downloaded (`storage/app/updates`). |
| `steps.migrate` | `PRODUCT_UPDATER_RUN_MIGRATIONS` | Run migrations after extraction (default `true`). |
| `steps.publish` | `PRODUCT_UPDATER_RUN_PUBLISH` | Publish assets after extraction (default `true`). |
| `publish.tag` | `PRODUCT_UPDATER_PUBLISH_TAG` | The vendor-publish tag to run. |
| `backup_env` | `PRODUCT_UPDATER_BACKUP_ENV` | Back up `.env` before applying (default `true`). |
| `backup_path` | `PRODUCT_UPDATER_BACKUP_PATH` | Where backups are written. |

## Sources

- **`http`** — a generic HTTP release endpoint (`HttpUpdateSource`).
- **`envato`** — CodeCanyon/Envato releases (`EnvatoUpdateSource`).

---

[← Docs index](../README.md#documentation)
