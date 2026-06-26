<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Listeners;

use Illuminate\Support\Facades\Log;

/**
 * Reacts to the verifier's license-loss events for observability. The updater
 * caches no "update available" state, so there is nothing to invalidate — the
 * existing download/extract license gate already blocks updates. This simply
 * records WHY updates will start being refused, so operators are not surprised.
 *
 * Methods take no typed event argument so this class never references the
 * verifier's (optional) event classes by type.
 */
final readonly class SyncLicenseState
{
    public function revoked(): void
    {
        $this->log('License revoked — product updates are now blocked.');
    }

    public function deactivated(): void
    {
        $this->log('License deactivated — product updates are now blocked.');
    }

    private function log(string $message): void
    {
        if (! (bool) config('product-updater.license_sync.enabled', true)) {
            return;
        }

        $channel = config('product-updater.license_sync.log_channel');

        is_string($channel) && $channel !== ''
            ? Log::channel($channel)->warning($message)
            : Log::warning($message);
    }
}
