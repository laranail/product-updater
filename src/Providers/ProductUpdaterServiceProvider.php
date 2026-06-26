<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Providers;

use Illuminate\Filesystem\Filesystem;
use Override;
use Simtabi\Laranail\Package\Tools\Package;
use Simtabi\Laranail\Package\Tools\Providers\PackageServiceProvider;
use Simtabi\Laranail\Product\Updater\Commands\CheckCommand;
use Simtabi\Laranail\Product\Updater\Commands\DoctorCommand;
use Simtabi\Laranail\Product\Updater\Commands\UpdateCommand;
use Simtabi\Laranail\Product\Updater\Contracts\UpdateSource;
use Simtabi\Laranail\Product\Updater\Sources\HttpUpdateSource;
use Simtabi\Laranail\Product\Updater\Support\EnvBackup;
use Simtabi\Laranail\Product\Updater\Support\UpdateLock;
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
            ->hasTranslations()
            ->hasCommands(CheckCommand::class, UpdateCommand::class, DoctorCommand::class)
            ->hasDoctorChecks(DoctorCommand::CHECKS);

        if (config('product-updater.api.enabled')) {
            $package->hasRoute('api');
        }
    }

    #[Override]
    public function packageBooted(): void
    {
        // Short translation namespace (hasTranslations() also registers the full
        // laranail/product-updater namespace) so keys read product-updater::…
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'product-updater');
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
            $app->make(EnvBackup::class),
            $app->make(UpdateLock::class),
        ));
    }
}
