<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Doctor;

use Simtabi\Laranail\Licence\Verifier\LicenseManager;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorResult;

/**
 * When updates are license-gated, the verifier must be installed and the
 * current license usable.
 */
final class LicenseCheck implements DoctorCheck
{
    public function name(): string
    {
        return 'product-updater:license';
    }

    public function description(): string
    {
        return 'A usable license is present when updates are license-gated';
    }

    public function run(): DoctorResult
    {
        if (! (bool) config('product-updater.require_license', true)) {
            return DoctorResult::skip('Updates are not license-gated.');
        }

        if (! class_exists(LicenseManager::class)) {
            return DoctorResult::fail('laranail/license-verifier is required but not installed.');
        }

        if (! app(LicenseManager::class)->verify()->isUsable()) {
            return DoctorResult::fail('The current license is not usable; updates will be blocked.');
        }

        return DoctorResult::pass('License is present and usable.');
    }
}
