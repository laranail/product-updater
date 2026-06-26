<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Sources;

use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Simtabi\Laranail\Product\Updater\Contracts\UpdateSource;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

/**
 * HTTP update source (Botble-style): POSTs check_update and streams
 * download_update to disk.
 */
final class HttpUpdateSource implements UpdateSource
{
    public function checkUpdate(string $productId, string $currentVersion): ?ProductRelease
    {
        $response = $this->http()->post('check_update', [
            'product_id' => $productId,
            'current_version' => $currentVersion,
            'channel' => config('product-updater.channel', 'stable'),
        ]);

        return $this->parse($response->json());
    }

    public function latest(string $productId): ?ProductRelease
    {
        return $this->checkUpdate($productId, '0.0.0');
    }

    public function download(string $updateId, string $destination, ?string $licenseToken = null): void
    {
        $this->http()
            ->withOptions(['sink' => $destination])
            ->post('download_update/'.$updateId, array_filter([
                'product_id' => config('product-updater.product_id'),
                'license_token' => $licenseToken,
            ]));
    }

    private function http(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('product-updater.source.url'), '/').'/')
            ->timeout((int) config('product-updater.timeout', 300))
            ->acceptJson()
            ->withHeaders(array_filter(['X-API-KEY' => config('product-updater.source.api_key')]));

        if (! (bool) config('product-updater.verify_tls', true)) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    private function parse(?array $data): ?ProductRelease
    {
        if (! $data || ! ($data['status'] ?? false) || empty($data['update_id']) || empty($data['version'])) {
            return null;
        }

        return new ProductRelease(
            updateId: (string) $data['update_id'],
            version: (string) $data['version'],
            releasedAt: isset($data['release_date']) ? Carbon::parse($data['release_date']) : Carbon::now(),
            summary: isset($data['summary']) ? trim((string) $data['summary']) : null,
            changelog: isset($data['changelog']) ? trim((string) $data['changelog']) : null,
            hasSql: (bool) ($data['has_sql'] ?? false),
        );
    }
}
