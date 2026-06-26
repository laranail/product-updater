<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Simtabi\Laranail\Product\Updater\Exceptions\UpdaterException;
use Simtabi\Laranail\Product\Updater\UpdateManager;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

it('detects an available update via the http source', function () {
    Http::fake(['updates.test/*' => Http::response([
        'status' => true,
        'update_id' => 'u-42',
        'version' => '1.2.0',
        'summary' => 'Bug fixes',
    ])]);

    $release = app(UpdateManager::class)->checkUpdate();

    expect($release)->toBeInstanceOf(ProductRelease::class)
        ->and($release->version)->toBe('1.2.0')
        ->and($release->isNewerThan('1.0.0'))->toBeTrue();
});

it('reports no update when the source returns the same version', function () {
    Http::fake(['updates.test/*' => Http::response(['status' => true, 'update_id' => 'u', 'version' => '1.0.0'])]);

    expect(app(UpdateManager::class)->checkUpdate())->toBeNull();
});

it('refuses to download when unlicensed', function () {
    config()->set('product-updater.require_license', true);
    config()->set('license-verifier.default', 'paseto'); // unactivated → not usable

    $release = new ProductRelease('u-1', '2.0.0');

    expect(fn () => app(UpdateManager::class)->download($release))
        ->toThrow(UpdaterException::class, 'valid license is required');
});

it('allows download when licensed via the null driver', function () {
    config()->set('product-updater.require_license', true);
    config()->set('license-verifier.default', 'null');

    Http::fake(['updates.test/*' => Http::response('PK'.str_repeat('0', 2048))]);

    // The fake "archive" is not a real zip, so validation fails *after* the
    // license gate — proving the gate itself passed.
    expect(fn () => app(UpdateManager::class)->download(new ProductRelease('u-1', '2.0.0')))
        ->toThrow(UpdaterException::class, 'archive is invalid');
});

it('rejects an archive containing a .env file', function () {
    $dir = sys_get_temp_dir().'/lv-zip-'.uniqid();
    mkdir($dir, 0755, true);
    $archive = $dir.'/update.zip';

    $zip = new ZipArchive();
    $zip->open($archive, ZipArchive::CREATE);
    $zip->addFromString('.env', 'APP_KEY=leak');
    $zip->addFromString('padding.bin', random_bytes(4096)); // incompressible → archive > 1KB
    $zip->close();

    config()->set('product-updater.require_license', false);

    expect(fn () => app(UpdateManager::class)->validateArchive($archive))
        ->toThrow(UpdaterException::class, 'contains a .env file');
});
