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
        $zip = new ZipArchive;

        if ($zip->open($archive) !== true) {
            return false;
        }

        try {
            // Refuse the whole archive if ANY entry would escape the destination
            // (zip-slip / path traversal) before writing a single file.
            $this->assertNoTraversal($zip, $destination);

            $extracted = $zip->extractTo($destination);
        } finally {
            $zip->close();
        }

        return $extracted;
    }

    public function contains(string $archive, string $entry): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($archive) !== true) {
            return false;
        }

        $found = $zip->locateName($entry, ZipArchive::FL_NOCASE) !== false;
        $zip->close();

        return $found;
    }

    public function read(string $archive, string $entry): ?string
    {
        $zip = new ZipArchive;

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
        $zip = new ZipArchive;
        $result = $zip->open($archive);

        if ($result !== true) {
            throw UpdaterException::invalidArchive("ZipArchive error code {$result}.");
        }

        $zip->close();
    }

    /**
     * Reject archives whose entries use absolute paths or `..` segments that
     * resolve outside the destination directory.
     *
     * @throws UpdaterException
     */
    private function assertNoTraversal(ZipArchive $zip, string $destination): void
    {
        $base = $this->normalize($destination);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if ($name === false) {
                continue;
            }

            if (str_contains($name, "\0")
                || str_starts_with($name, '/')
                || str_starts_with($name, '\\')
                || preg_match('#^[a-zA-Z]:#', $name) === 1) {
                throw UpdaterException::unsafeArchive($name);
            }

            $target = $this->normalize($destination.'/'.$name);

            if ($target !== $base && ! str_starts_with($target, $base.'/')) {
                throw UpdaterException::unsafeArchive($name);
            }
        }
    }

    /**
     * Resolve `.`/`..` segments lexically (the path need not exist), normalising
     * separators to `/` for a safe prefix comparison.
     */
    private function normalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $absolute = str_starts_with($path, '/');

        $parts = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '') {
                continue;
            }
            if ($segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);

                continue;
            }

            $parts[] = $segment;
        }

        return ($absolute ? '/' : '').implode('/', $parts);
    }
}
