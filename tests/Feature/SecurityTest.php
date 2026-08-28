<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Simtabi\Laranail\Product\Updater\UpdateManager;
use Simtabi\Laranail\Product\Updater\Support\Zipper;
use Simtabi\Laranail\Product\Updater\Events\IntegrityCheckFailed;
use Simtabi\Laranail\Product\Updater\Exceptions\UpdaterException;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

/**
 * Build a real (incompressible, >1KB) zip and return [path, bytes].
 *
 * @return array{0: string, 1: string}
 */
function makeZip(array $entries): array
{
    $path = tempnam(sys_get_temp_dir(), 'lv-zip-') . '.zip';
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($entries as $name => $content) {
        $zip->addFromString($name, $content);
    }
    $zip->addFromString('pad.bin', random_bytes(4096));
    $zip->close();

    return [$path, (string) file_get_contents($path)];
}

it('refuses to extract an archive with a path-traversal entry (zip-slip)', function (): void {
    [$archive] = makeZip(['../escaped.txt' => 'pwned']);
    $dest = sys_get_temp_dir() . '/lv-extract-' . uniqid();
    mkdir($dest, 0755, true);

    expect(fn () => app(Zipper::class)->extract($archive, $dest))
        ->toThrow(UpdaterException::class, 'unsafe path');

    // Nothing was written outside the destination.
    expect(file_exists(dirname($dest) . '/escaped.txt'))->toBeFalse();
});

it('refuses an archive with an absolute-path entry', function (): void {
    [$archive] = makeZip(['/etc/evil.txt' => 'pwned']);
    $dest = sys_get_temp_dir() . '/lv-extract-' . uniqid();
    mkdir($dest, 0755, true);

    expect(fn () => app(Zipper::class)->extract($archive, $dest))
        ->toThrow(UpdaterException::class, 'unsafe path');
});

it('extracts a safe archive into the destination', function (): void {
    [$archive] = makeZip(['app/file.txt' => 'ok']);
    $dest = sys_get_temp_dir() . '/lv-extract-' . uniqid();
    mkdir($dest, 0755, true);

    expect(app(Zipper::class)->extract($archive, $dest))->toBeTrue()
        ->and(file_get_contents($dest . '/app/file.txt'))->toBe('ok');
});

it('aborts the download when the archive checksum does not match', function (): void {
    config()->set('product-updater.require_license', false);
    config()->set('product-updater.verify_checksum', true);

    [, $bytes] = makeZip(['app/x.txt' => 'hello']);
    Http::fake(['updates.test/*' => Http::response($bytes)]);
    Event::fake([IntegrityCheckFailed::class]);

    $release = new ProductRelease('u-1', '2.0.0', checksum: str_repeat('a', 64));

    expect(fn () => app(UpdateManager::class)->download($release))
        ->toThrow(UpdaterException::class, 'integrity check failed');

    Event::assertDispatched(IntegrityCheckFailed::class);
});

it('accepts the download when the checksum matches', function (): void {
    config()->set('product-updater.require_license', false);
    config()->set('product-updater.verify_checksum', true);

    [, $bytes] = makeZip(['app/x.txt' => 'hello']);
    Http::fake(['updates.test/*' => Http::response($bytes)]);

    $release = new ProductRelease('u-1', '2.0.0', checksum: hash('sha256', $bytes));

    $path = app(UpdateManager::class)->download($release);

    expect($path)->toBeString()->and(file_exists($path))->toBeTrue();
});
