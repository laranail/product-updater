<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Support;

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Product\Updater\Events\EnvBackedUp;
use Simtabi\Laranail\Product\Updater\Events\EnvRestored;

/**
 * Backs up and restores the host application's .env around an update so a
 * failed update can roll the configuration back.
 */
final readonly class EnvBackup
{
    public function __construct(private Filesystem $files) {}

    /**
     * Copy the host .env to a timestamped backup. Returns the backup path, or
     * null when backups are disabled or there is no .env to back up.
     */
    public function backup(): ?string
    {
        if (! (bool) config('product-updater.backup_env', true)) {
            return null;
        }

        $env = base_path('.env');

        if (! $this->files->exists($env)) {
            return null;
        }

        $dir = $this->backupDir();
        $this->files->ensureDirectoryExists($dir);

        $destination = $dir.'/.env.backup-'.date('Ymd_His');
        $this->files->copy($env, $destination);

        EnvBackedUp::dispatch($destination);
        $this->prune();

        return $destination;
    }

    /**
     * Restore the host .env from a previously created backup.
     */
    public function restore(string $backup): void
    {
        if (! $this->files->exists($backup)) {
            return;
        }

        $this->files->copy($backup, base_path('.env'));

        EnvRestored::dispatch($backup);
    }

    /**
     * Keep only the most recent N backups.
     */
    public function prune(?int $keep = null): void
    {
        $keep ??= (int) config('product-updater.backup_keep', 5);
        $dir = $this->backupDir();

        if (! $this->files->isDirectory($dir)) {
            return;
        }

        $backups = collect($this->files->files($dir))
            ->filter(static fn ($file): bool => str_starts_with($file->getFilename(), '.env.backup-'))
            ->sortByDesc(static fn ($file): string => $file->getFilename())
            ->values();

        $backups->slice($keep)->each(fn ($file): bool => $this->files->delete($file->getPathname()));
    }

    private function backupDir(): string
    {
        return (string) config('product-updater.backup_path', storage_path('app/updates/backups'));
    }
}
