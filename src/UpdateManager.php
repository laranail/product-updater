<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater;

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Licence\Verifier\LicenseManager;
use Simtabi\Laranail\Product\Updater\Contracts\UpdateSource;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateAvailable;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateCachesCleared;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateCachesClearing;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateChecked;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateChecking;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateDownloaded;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateDownloading;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateExtractedFiles;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateUnavailable;
use Simtabi\Laranail\Product\Updater\Exceptions\UpdaterException;
use Simtabi\Laranail\Product\Updater\Support\Zipper;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

/**
 * Orchestrates the self-update flow, gated by laranail/license-verifier.
 */
final readonly class UpdateManager
{
    public function __construct(
        private UpdateSource $source,
        private Zipper $zip,
        private Filesystem $files,
    ) {}

    public function currentVersion(): string
    {
        return (string) config('product-updater.current_version', '1.0.0');
    }

    public function productId(): string
    {
        return (string) config('product-updater.product_id');
    }

    /**
     * Check the source for a newer release.
     */
    public function checkUpdate(): ?ProductRelease
    {
        SystemUpdateChecking::dispatch();

        $release = $this->source->checkUpdate($this->productId(), $this->currentVersion());

        SystemUpdateChecked::dispatch();

        if ($release instanceof ProductRelease && $release->isNewerThan($this->currentVersion())) {
            SystemUpdateAvailable::dispatch($release);

            return $release;
        }

        SystemUpdateUnavailable::dispatch();

        return null;
    }

    public function latest(): ?ProductRelease
    {
        return $this->source->latest($this->productId());
    }

    /**
     * Download (and validate) a release archive. License-gated.
     */
    public function download(ProductRelease $release): string
    {
        $this->ensureLicensed();
        $this->ensureExtensions();

        SystemUpdateDownloading::dispatch();

        $destination = $this->downloadPath($release->version);
        $this->files->ensureDirectoryExists(dirname($destination));

        $this->source->download($release->updateId, $destination, $this->licenseToken());

        $this->validateArchive($destination);

        SystemUpdateDownloaded::dispatch($destination);

        return $destination;
    }

    /**
     * Extract a downloaded archive over the application base path. License-gated.
     */
    public function extract(string $archive): bool
    {
        $this->ensureLicensed();
        $this->validateArchive($archive);

        $extracted = $this->zip->extract($archive, (string) config('product-updater.paths.base', base_path()));

        if ($extracted) {
            SystemUpdateExtractedFiles::dispatch();
            $this->clearCaches();
            $this->files->delete($archive);
        }

        return $extracted;
    }

    public function clearCaches(): void
    {
        SystemUpdateCachesClearing::dispatch();

        rescue(fn () => app('cache')->clear());

        SystemUpdateCachesCleared::dispatch();
    }

    /**
     * Validate an archive (ported from Botble's validateUpdateFile): reject a
     * bundled .env, ensure the zip opens and is not suspiciously small.
     */
    public function validateArchive(string $archive): void
    {
        if (! $this->files->exists($archive)) {
            throw UpdaterException::invalidArchive('file not found.');
        }

        if ((int) $this->files->size($archive) < 1024) {
            throw UpdaterException::invalidArchive('file is too small / likely corrupted.');
        }

        $this->zip->assertValid($archive);

        if ($this->zip->contains($archive, '.env')) {
            throw UpdaterException::invalidArchive('it contains a .env file.');
        }
    }

    private function ensureLicensed(): void
    {
        if (! (bool) config('product-updater.require_license', true)) {
            return;
        }

        $manager = app(LicenseManager::class);

        if (! $manager->verify()->isUsable()) {
            throw UpdaterException::requiresLicense();
        }

        $entitlement = config('product-updater.require_entitlement');

        if ($entitlement && ! $manager->entitledTo((string) $entitlement)) {
            throw UpdaterException::requiresEntitlement((string) $entitlement);
        }
    }

    private function ensureExtensions(): void
    {
        if (! extension_loaded('zip')) {
            throw UpdaterException::missingZipExtension();
        }

        if (! extension_loaded('curl')) {
            throw UpdaterException::missingCurlExtension();
        }
    }

    private function licenseToken(): ?string
    {
        return app(LicenseManager::class)->currentToken();
    }

    private function downloadPath(string $version): string
    {
        $dir = (string) config('product-updater.paths.download', storage_path('app/updates'));

        return $dir.'/update_'.str_replace('.', '_', $version).'.zip';
    }
}
