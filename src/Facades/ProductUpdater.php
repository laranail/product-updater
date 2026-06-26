<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Product\Updater\UpdateManager;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

/**
 * @method static string currentVersion()
 * @method static ProductRelease|null checkUpdate()
 * @method static ProductRelease|null latest()
 * @method static string download(ProductRelease $release)
 * @method static bool extract(string $archive)
 * @method static void validateArchive(string $archive)
 * @method static void clearCaches()
 *
 * @see UpdateManager
 */
final class ProductUpdater extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UpdateManager::class;
    }
}
