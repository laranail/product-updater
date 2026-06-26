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

    public static function productMismatch(): self
    {
        return new self('The update archive product ID does not match this installation.');
    }

    public static function downgrade(): self
    {
        return new self('The update archive version is older than the installed version.');
    }
}
