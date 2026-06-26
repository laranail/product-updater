<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Exceptions;

use RuntimeException;

class UpdaterException extends RuntimeException
{
    public static function missingZipExtension(): self
    {
        return new self('The PHP "zip" extension is required to apply updates.');
    }

    public static function missingCurlExtension(): self
    {
        return new self('The PHP "curl" extension is required to download updates.');
    }

    public static function requiresLicense(): self
    {
        return new self('A valid license is required to download or apply updates.');
    }

    public static function requiresEntitlement(string $entitlement): self
    {
        return new self(sprintf('Your license does not include the "%s" entitlement required for updates.', $entitlement));
    }

    public static function invalidArchive(string $reason): self
    {
        return new self('The update archive is invalid: '.$reason);
    }

    public static function unsafeArchive(string $entry): self
    {
        return new self(sprintf('The update archive contains an unsafe path "%s" (path traversal); refusing to extract.', $entry));
    }

    public static function checksumMismatch(string $expected, string $actual): self
    {
        return new self(sprintf('Update archive integrity check failed: expected SHA-256 %s but got %s.', $expected, $actual));
    }

    public static function signatureInvalid(): self
    {
        return new self('Update archive signature verification failed.');
    }

    public static function requirementsFailed(string $reason): self
    {
        return new self('System requirements for this update are not met: '.$reason);
    }

    public static function productMismatch(): self
    {
        return new self('The update archive product ID does not match this installation.');
    }

    public static function downgrade(): self
    {
        return new self('The update archive version is older than the installed version.');
    }
}
