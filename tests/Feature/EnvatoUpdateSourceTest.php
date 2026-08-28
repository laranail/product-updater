<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Simtabi\Laranail\Product\Updater\Contracts\UpdateSource;
use Simtabi\Laranail\Product\Updater\Sources\EnvatoUpdateSource;

beforeEach(function (): void {
    config()->set('product-updater.source.url', 'https://updates.test');
    config()->set('product-updater.source.api_key', 'KEY-123');
});

it('checks connection, parses a release (with checksum/signature/min_php) and size, and sends LB headers', function (): void {
    Http::fake([
        'updates.test/check_connection_ext' => Http::response('', 200),
        'updates.test/check_update'         => Http::response([
            'status'    => true,
            'update_id' => 'u-1',
            'version'   => '2.0.0',
            'checksum'  => 'sha256:abc',
            'signature' => 'sig-xyz',
            'min_php'   => '8.4',
            'has_sql'   => true,
        ]),
        'updates.test/get_update_size/*' => Http::response('', 200, ['Content-Length' => '4096']),
    ]);

    $source = new EnvatoUpdateSource;

    $release = $source->checkUpdate('prod', '1.0.0');

    expect($source->checkConnection())->toBeTrue()
        ->and($release->checksum)->toBe('sha256:abc')
        ->and($release->signature)->toBe('sig-xyz')
        ->and($release->minPhp)->toBe('8.4')
        ->and($release->hasSql)->toBeTrue()
        ->and($source->getUpdateSize('u-1'))->toBe(4096);

    // LB-* headers identify the install to the license bridge.
    Http::assertSent(fn ($request): bool => $request->hasHeader('LB-API-KEY', 'KEY-123') && $request->hasHeader('LB-LANG', 'english'));
});

it('is selected by the envato source driver', function (): void {
    config()->set('product-updater.source.driver', 'envato');

    expect(app(UpdateSource::class))->toBeInstanceOf(EnvatoUpdateSource::class);
});
