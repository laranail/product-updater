<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Simtabi\Laranail\Licence\Verifier\Providers\LicenceVerifierServiceProvider;
use Simtabi\Laranail\Product\Updater\Providers\ProductUpdaterServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LicenceVerifierServiceProvider::class,
            ProductUpdaterServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('license-verifier.heartbeat.enabled', false);
        $app['config']->set('product-updater.source.url', 'https://updates.test');
        $app['config']->set('product-updater.product_id', 'PROD-1');
        $app['config']->set('product-updater.current_version', '1.0.0');
        $app['config']->set('product-updater.paths.download', sys_get_temp_dir().'/lv-updates-'.uniqid());
    }
}
