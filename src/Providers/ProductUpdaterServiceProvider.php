<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Providers;

use Illuminate\Filesystem\Filesystem;
use Override;
use Simtabi\Laranail\Licence\Verifier\Drivers\DriverManager;
use Simtabi\Laranail\Package\Tools\Package;
use Simtabi\Laranail\Package\Tools\Providers\PackageServiceProvider;
use Simtabi\Laranail\Product\Updater\Commands\CheckCommand;
use Simtabi\Laranail\Product\Updater\Commands\UpdateCommand;
use Simtabi\Laranail\Product\Updater\Contracts\UpdateSource;
use Simtabi\Laranail\Product\Updater\Sources\HttpUpdateSource;
use Simtabi\Laranail\Product\Updater\Support\Zipper;
use Simtabi\Laranail\Product\Updater\UpdateManager;

final class ProductUpdaterServiceProvider extends PackageServiceProvider
{
    #[Override]
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laranail/product-updater')
            ->hasConfigFile('product-updater')
            ->hasCommands(CheckCommand::class, UpdateCommand::class);
    }

    #[Override]
    public function packageRegistered(): void
    {
        $this->app->bind(UpdateSource::class, static fn (): UpdateSource => match ((string) config('product-updater.source.driver', 'http')) {
            default => new HttpUpdateSource,
        });

        $this->app->singleton(UpdateManager::class, static fn ($app): UpdateManager => new UpdateManager(
            $app->make(UpdateSource::class),
            $app->make(Zipper::class),
            $app->make(Filesystem::class),
            $app->make(DriverManager::class),
        ));
    }
}
