<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('registers the product-updater translation namespace', function (): void {
    expect(__('laranail-product-updater::product-updater.update.up_to_date'))->toBe('Already up to date.')
        ->and(__('laranail-product-updater::product-updater.check.available', ['version' => '2.0.0']))->toBe('Update available: v2.0.0');
});

it('renders translated command output', function (): void {
    Http::fake(['updates.test/*' => Http::response(['status' => true, 'update_id' => 'u', 'version' => '1.0.0'])]);

    $this->artisan('laranail::product-updater.check')
        ->expectsOutputToContain('running the latest version (1.0.0)')
        ->assertExitCode(0);
});
