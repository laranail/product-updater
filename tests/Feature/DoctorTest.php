<?php

declare(strict_types=1);

use Simtabi\Laranail\Product\Updater\Doctor\LicenseCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorStatus;
use Simtabi\Laranail\Package\Tools\Services\Doctor\Checks\ConfigPresentCheck;

/** The updater's source-url + product-id config check (now a reusable ConfigPresentCheck). */
function updaterConfigCheck(): ConfigPresentCheck
{
    return new ConfigPresentCheck(['product-updater.source.url', 'product-updater.product_id']);
}

it('fails the configuration check when source url / product id are missing', function (): void {
    config()->set('product-updater.source.url');
    config()->set('product-updater.product_id');

    expect(updaterConfigCheck()->run()->status)->toBe(DoctorStatus::Fail);
});

it('passes the configuration check when source url + product id are set', function (): void {
    config()->set('product-updater.source.url', 'https://updates.test');
    config()->set('product-updater.product_id', 'demo');

    expect(updaterConfigCheck()->run()->status)->toBe(DoctorStatus::Pass);
});

it('skips the license check when updates are not license-gated', function (): void {
    config()->set('product-updater.require_license', false);

    expect((new LicenseCheck)->run()->status)->toBe(DoctorStatus::Skip);
});

it('runs the doctor command without error', function (): void {
    config()->set('product-updater.source.url', 'https://updates.test');
    config()->set('product-updater.product_id', 'demo');
    config()->set('product-updater.require_license', false);

    $this->artisan('laranail::product-updater.doctor --json')->run();

    expect(true)->toBeTrue();
});
