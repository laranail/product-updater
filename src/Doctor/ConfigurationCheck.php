<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Doctor;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorResult;

/**
 * The host must provide an update source URL and a product id.
 */
final class ConfigurationCheck implements DoctorCheck
{
    public function name(): string
    {
        return 'product-updater:config';
    }

    public function description(): string
    {
        return 'Update source URL and product id are configured';
    }

    public function run(): DoctorResult
    {
        $url = config('product-updater.source.url');
        $productId = config('product-updater.product_id');

        $missing = [];

        if (! is_string($url) || $url === '') {
            $missing[] = 'PRODUCT_UPDATER_URL';
        }

        if (! is_string($productId) || $productId === '') {
            $missing[] = 'PRODUCT_UPDATER_PRODUCT_ID';
        }

        if ($missing !== []) {
            return DoctorResult::fail('Missing required config.', ['missing' => $missing]);
        }

        return DoctorResult::pass('Source URL and product id are set.');
    }
}
