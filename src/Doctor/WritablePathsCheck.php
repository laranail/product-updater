<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Doctor;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorResult;

/**
 * The download dir and install base must be writable, with enough free disk.
 */
final class WritablePathsCheck implements DoctorCheck
{
    /** Warn below this much free space (bytes) for the download/extract. */
    private const int MIN_FREE_BYTES = 64 * 1024 * 1024;

    public function name(): string
    {
        return 'product-updater:paths';
    }

    public function description(): string
    {
        return 'Download/install paths are writable with free disk space';
    }

    public function run(): DoctorResult
    {
        $download = (string) config('product-updater.paths.download', storage_path('app/updates'));
        $base = (string) config('product-updater.paths.base', base_path());

        $notWritable = [];

        foreach (['download' => $download, 'base' => $base] as $label => $path) {
            $probe = is_dir($path) ? $path : dirname($path);

            if (! is_writable($probe)) {
                $notWritable[$label] = $path;
            }
        }

        if ($notWritable !== []) {
            return DoctorResult::fail('Path is not writable.', $notWritable);
        }

        $free = (int) (@disk_free_space(is_dir($download) ? $download : dirname($download)) ?: 0);

        if ($free > 0 && $free < self::MIN_FREE_BYTES) {
            return DoctorResult::warn('Low free disk space for updates.', ['free_bytes' => $free]);
        }

        return DoctorResult::pass('Paths writable; sufficient disk space.');
    }
}
