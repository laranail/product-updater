<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Doctor;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorResult;

/**
 * Downloading and applying updates needs the zip and curl extensions.
 */
final class ExtensionsCheck implements DoctorCheck
{
    public function name(): string
    {
        return 'product-updater:extensions';
    }

    public function description(): string
    {
        return 'PHP zip and curl extensions are loaded';
    }

    public function run(): DoctorResult
    {
        $missing = array_values(array_filter(
            ['zip', 'curl'],
            static fn (string $ext): bool => ! extension_loaded($ext),
        ));

        if ($missing !== []) {
            return DoctorResult::fail('Required PHP extensions are missing.', ['missing' => $missing]);
        }

        return DoctorResult::pass('zip and curl are loaded.');
    }
}
