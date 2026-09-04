<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Simtabi\Laranail\Product\Updater\UpdateManager;
use Simtabi\Laranail\Product\Updater\Events\RequirementsFailed;
use Simtabi\Laranail\Product\Updater\Exceptions\UpdaterException;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateDBMigrated;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateDBMigrating;
use Simtabi\Laranail\Product\Updater\Events\SystemUpdateExtractedFiles;

function applyZip(array $entries): string
{
    $path = tempnam(sys_get_temp_dir(), 'lv-apply-') . '.zip';
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($entries as $name => $content) {
        $zip->addFromString($name, $content);
    }
    $zip->addFromString('pad.bin', random_bytes(4096));
    $zip->close();

    return $path;
}

function tempBase(): string
{
    $base = sys_get_temp_dir() . '/lv-base-' . uniqid();
    mkdir($base, 0755, true);

    return $base;
}

beforeEach(function (): void {
    config()->set('product-updater.require_license', false);
    config()->set('product-updater.backup_env', false);
    config()->set('product-updater.steps.migrate', false);
    config()->set('product-updater.steps.publish', false);
    config()->set('product-updater.paths.download', sys_get_temp_dir() . '/lv-dl-' . uniqid());
});

it('aborts the download when the release requires a newer PHP', function (): void {
    Event::fake([RequirementsFailed::class]);

    expect(fn () => app(UpdateManager::class)->download(new ProductRelease('u-1', '2.0.0', minPhp: '99.0')))
        ->toThrow(UpdaterException::class, 'requirements');

    Event::assertDispatched(RequirementsFailed::class);
});

it('promotes a staged extract over the configured base and fires the extract event', function (): void {
    $base = tempBase();
    config()->set('product-updater.paths.base', $base);

    Event::fake([SystemUpdateExtractedFiles::class]);

    expect(app(UpdateManager::class)->extract(applyZip(['app/new.txt' => 'updated'])))->toBeTrue()
        ->and(file_get_contents($base . '/app/new.txt'))->toBe('updated');

    Event::assertDispatched(SystemUpdateExtractedFiles::class);
});

it('dispatches the migrate events when the migrate step is enabled', function (): void {
    config()->set('product-updater.paths.base', tempBase());
    config()->set('product-updater.steps.migrate', true);

    Event::fake([SystemUpdateDBMigrating::class, SystemUpdateDBMigrated::class]);

    app(UpdateManager::class)->extract(applyZip(['app/x.txt' => 'y']));

    Event::assertDispatched(SystemUpdateDBMigrating::class);
    Event::assertDispatched(SystemUpdateDBMigrated::class);
});
