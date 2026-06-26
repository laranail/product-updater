<?php

declare(strict_types=1);

use Simtabi\Laranail\Product\Updater\Http\Controllers\HealthController;

beforeEach(function (): void {
    config()->set('product-updater.require_license', false);
});

it('reports the doctor checks as JSON with a status', function (): void {
    config()->set('product-updater.source.url', 'https://updates.test');
    config()->set('product-updater.product_id', 'demo');

    $data = app(HealthController::class)->show()->getData(true);

    expect($data)->toHaveKeys(['status', 'checks']);

    $config = collect($data['checks'])->firstWhere('name', 'product-updater:config');
    expect($config['status'])->toBe('pass');
});

it('returns 503 degraded when configuration is missing', function (): void {
    config()->set('product-updater.source.url');
    config()->set('product-updater.product_id');

    $response = app(HealthController::class)->show();

    expect($response->getStatusCode())->toBe(503)
        ->and($response->getData(true)['status'])->toBe('degraded');
});
