<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Product\Updater\UpdateManager;

/**
 * @method static string currentVersion()
 * @method static \Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease|null checkUpdate()
 * @method static \Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease|null latest()
 * @method static string download(\Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease $release)
 * @method static bool extract(string $archive)
 * @method static void validateArchive(string $archive)
 * @method static void clearCaches()
 *
 * @see \Simtabi\Laranail\Product\Updater\UpdateManager
 */
final class ProductUpdater extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UpdateManager::class;
    }
}
