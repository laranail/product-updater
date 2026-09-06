<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Doctor;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\Checks\PhpExtensionCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\Checks\ConfigPresentCheck;

/**
 * The canonical product-updater health checks — one list reused by the service
 * provider (unified doctor), the doctor command, and the HTTP health endpoint.
 */
final class Checks
{
    /**
     * @return list<DoctorCheck|class-string<DoctorCheck>>
     */
    public static function all(): array
    {
        return [
            new ConfigPresentCheck(
                ['PRODUCT_UPDATER_URL' => 'product-updater.source.url', 'PRODUCT_UPDATER_PRODUCT_ID' => 'product-updater.product_id'],
                name: 'product-updater:config',
                description: 'Update source URL and product id are configured',
            ),
            new PhpExtensionCheck(['zip', 'curl'], 'product-updater:extensions', 'PHP zip and curl extensions are loaded'),
            WritablePathsCheck::class,
            LicenseCheck::class,
        ];
    }
}
