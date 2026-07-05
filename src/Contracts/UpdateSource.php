<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Contracts;

use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

/**
 * A source that lists and serves product releases.
 */
interface UpdateSource
{
    /**
     * The newest release available for the product, or null if none/up-to-date.
     */
    public function checkUpdate(string $productId, string $currentVersion): ?ProductRelease;

    /**
     * The latest release regardless of the current version.
     */
    public function latest(string $productId): ?ProductRelease;

    /**
     * Download a release archive to $destination. A license token may be
     * supplied to authorize the download.
     */
    public function download(string $updateId, string $destination, ?string $licenseToken = null): void;

    /**
     * Whether the update server is reachable (pre-flight before a download).
     */
    public function checkConnection(): bool;

    /**
     * The size in bytes of a release archive (for disk-space pre-checks), or null
     * if the server does not report it.
     */
    public function getUpdateSize(string $updateId): ?int;
}
