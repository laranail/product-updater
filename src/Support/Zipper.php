<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Support;

use Simtabi\Laranail\Product\Updater\Exceptions\UpdaterException;
use ZipArchive;

/**
 * Thin ZipArchive wrapper for extracting and inspecting update archives.
 */
final class Zipper
{
    public function extract(string $archive, string $destination): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($archive) !== true) {
            return false;
        }

        $extracted = $zip->extractTo($destination);
        $zip->close();

        return $extracted;
    }

    public function contains(string $archive, string $entry): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($archive) !== true) {
            return false;
        }

        $found = $zip->locateName($entry, ZipArchive::FL_NOCASE) !== false;
        $zip->close();

        return $found;
    }

    public function read(string $archive, string $entry): ?string
    {
        $zip = new ZipArchive();

        if ($zip->open($archive) !== true) {
            return null;
        }

        $contents = $zip->getFromName($entry);
        $zip->close();

        return $contents === false ? null : $contents;
    }

    /**
     * Assert the archive opens cleanly, mapping ZipArchive error codes.
     */
    public function assertValid(string $archive): void
    {
        $zip = new ZipArchive();
        $result = $zip->open($archive);

        if ($result !== true) {
            throw UpdaterException::invalidArchive("ZipArchive error code {$result}.");
        }

        $zip->close();
    }
}
