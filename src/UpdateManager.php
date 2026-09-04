<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater;

use Throwable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Simtabi\Laranail\Product\Updater\Support\Zipper;
use Simtabi\Laranail\Licence\Verifier\LicenseManager;
use Simtabi\Laranail\Product\Updater\Support\EnvBackup;
use Simtabi\Laranail\Product\Updater\Support\UpdateLock;
use Simtabi\Laranail\Product\Updater\Contracts\UpdateSource;
use Simtabi\Laranail\Product\Updater\Doctor\WritablePathsCheck;
use Simtabi\Laranail\Product\Updater\Events\RequirementsFailed;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorStatus;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateChecked;
use Simtabi\Laranail\Product\Updater\Events\IntegrityCheckFailed;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateChecking;
use Simtabi\Laranail\Product\Updater\Exceptions\UpdaterException;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateAvailable;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdatePublished;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateDBMigrated;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateDownloaded;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdatePublishing;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateDBMigrating;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateDownloading;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateUnavailable;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateCachesCleared;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateCachesClearing;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateExtractedFiles;

/**
 * Orchestrates the self-update flow, gated by laranail/license-verifier.
 */
final readonly class UpdateManager
{
    public function __construct(
        private UpdateSource $source,
        private Zipper $zip,
        private Filesystem $files,
        private EnvBackup $envBackup,
        private UpdateLock $lock,
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
        $this->ensureRequirements($release);

        SystemUpdateDownloading::dispatch();

        $destination = $this->downloadPath($release->version);
        $this->files->ensureDirectoryExists(dirname($destination));

        $this->source->download($release->updateId, $destination, $this->licenseToken());

        $this->validateArchive($destination);
        $this->verifyIntegrity($release, $destination);

        SystemUpdateDownloaded::dispatch($destination);

        return $destination;
    }

    /**
     * Apply a downloaded archive: back up .env, extract to a staging dir, promote
     * over the application base, run migrations + asset publishing, then clear
     * caches. Serialised by a lock; restores .env on any failure. License-gated.
     */
    public function extract(string $archive): bool
    {
        $this->ensureLicensed();
        $this->validateArchive($archive);

        return $this->lock->run(function () use ($archive): bool {
            $backup = $this->envBackup->backup();
            $staging = $this->stagingDir();

            try {
                $this->files->ensureDirectoryExists($staging);

                if (! $this->zip->extract($archive, $staging)) {
                    throw UpdaterException::invalidArchive('extraction failed.');
                }

                $this->files->copyDirectory($staging, (string) config('product-updater.paths.base', base_path()));
                SystemUpdateExtractedFiles::dispatch();

                $this->runMigrations();
                $this->publishAssets();

                $this->clearCaches();
                $this->files->delete($archive);
                $this->files->deleteDirectory($staging);

                return true;
            } catch (Throwable $e) {
                if ($backup !== null) {
                    $this->envBackup->restore($backup);
                }

                $this->files->deleteDirectory($staging);

                throw $e instanceof UpdaterException ? $e : UpdaterException::invalidArchive($e->getMessage());
            }
        });
    }

    public function clearCaches(): void
    {
        SystemUpdateCachesClearing::dispatch();

        rescue(fn () => app('cache')->clear());

        SystemUpdateCachesCleared::dispatch();
    }

    /**
     * Validate an archive: reject a bundled .env, ensure the zip opens and is
     * not suspiciously small.
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

    /**
     * Verify the downloaded archive against the source-published SHA-256 (and an
     * optional detached signature when a public key is configured). Deletes the
     * file and aborts before extraction on any mismatch.
     */
    private function verifyIntegrity(ProductRelease $release, string $path): void
    {
        if (! (bool) config('product-updater.verify_checksum', true)) {
            return;
        }

        if ($release->checksum !== null && $release->checksum !== '') {
            $actual = hash_file('sha256', $path) ?: '';

            if (! hash_equals(strtolower($release->checksum), strtolower($actual))) {
                $this->files->delete($path);
                IntegrityCheckFailed::dispatch($path, 'checksum mismatch');

                throw UpdaterException::checksumMismatch($release->checksum, $actual);
            }
        }

        $publicKey = config('product-updater.public_key');

        if (is_string($publicKey) && $publicKey !== '' && $release->signature !== null && $release->signature !== '') {
            $valid = openssl_verify(
                (string) $this->files->get($path),
                base64_decode($release->signature, true) ?: '',
                $publicKey,
                OPENSSL_ALGO_SHA256,
            ) === 1;

            if (! $valid) {
                $this->files->delete($path);
                IntegrityCheckFailed::dispatch($path, 'signature invalid');

                throw UpdaterException::signatureInvalid();
            }
        }
    }

    /**
     * Run the host's pending migrations (config-toggleable; off-by-default hosts
     * that manage their own migrations can opt out).
     */
    private function runMigrations(): void
    {
        if (! (bool) config('product-updater.steps.migrate', true)) {
            return;
        }

        SystemUpdateDBMigrating::dispatch();
        Artisan::call('migrate', ['--force' => true]);
        SystemUpdateDBMigrated::dispatch();
    }

    /**
     * Publish a configured vendor:publish tag's assets (no-op unless a tag is set,
     * to avoid clobbering host customisations).
     */
    private function publishAssets(): void
    {
        $tag = config('product-updater.publish.tag');

        if (! (bool) config('product-updater.steps.publish', true) || ! is_string($tag) || $tag === '') {
            return;
        }

        SystemUpdatePublishing::dispatch();
        Artisan::call('vendor:publish', ['--tag' => $tag, '--force' => true]);
        SystemUpdatePublished::dispatch();
    }

    private function stagingDir(): string
    {
        return config('product-updater.paths.download', storage_path('app/updates')) . '/staging';
    }

    /**
     * Enforce the system requirements for a release before any file is written:
     * the declared PHP floor and the writable-paths/disk doctor check.
     */
    private function ensureRequirements(ProductRelease $release): void
    {
        if ($release->minPhp !== null && version_compare(PHP_VERSION, $release->minPhp, '<')) {
            $this->failRequirements(sprintf('PHP %s or higher is required (running %s).', $release->minPhp, PHP_VERSION));
        }

        $paths = (new WritablePathsCheck)->run();

        if ($paths->status === DoctorStatus::Fail) {
            $this->failRequirements($paths->message);
        }
    }

    private function failRequirements(string $reason): never
    {
        RequirementsFailed::dispatch($reason);

        throw UpdaterException::requirementsFailed($reason);
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

        return $dir . '/update_' . str_replace('.', '_', $version) . '.zip';
    }
}
