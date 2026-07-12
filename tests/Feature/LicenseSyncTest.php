<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Simtabi\Laranail\Licence\Verifier\Events\LicenseDeactivated;
use Simtabi\Laranail\Licence\Verifier\Events\LicenseRevoked;

it('logs when the verifier reports the license revoked', function (): void {
    Log::spy();

    event(new LicenseRevoked('K', 'Acme'));

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message): bool => str_contains($message, 'revoked'))
        ->once();
});

it('logs when the verifier reports the license deactivated', function (): void {
    Log::spy();

    event(new LicenseDeactivated('K', 'Acme'));

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message): bool => str_contains($message, 'deactivated'))
        ->once();
});

it('does not log when license_sync is disabled', function (): void {
    config()->set('product-updater.license_sync.enabled', false);
    Log::spy();

    event(new LicenseRevoked('K', 'Acme'));

    Log::shouldNotHaveReceived('warning');
});
