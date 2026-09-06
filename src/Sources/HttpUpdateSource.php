<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Sources;

use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Simtabi\Laranail\Product\Updater\Contracts\UpdateSource;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

/**
 * HTTP update source (Botble-style): POSTs check_update and streams
 * download_update to disk.
 */
class HttpUpdateSource implements UpdateSource
{
    public function checkUpdate(string $productId, string $currentVersion): ?ProductRelease
    {
        $response = $this->http()->post('check_update', [
            'product_id'      => $productId,
            'current_version' => $currentVersion,
            'channel'         => config('product-updater.channel', 'stable'),
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
            ->post('download_update/' . $updateId, array_filter([
                'product_id'    => config('product-updater.product_id'),
                'license_token' => $licenseToken,
            ]));
    }

    public function checkConnection(): bool
    {
        try {
            return $this->http()->get('check_connection')->successful();
        } catch (Throwable) {
            return false;
        }
    }

    public function getUpdateSize(string $updateId): ?int
    {
        $length = $this->http()->head('get_update_size/' . $updateId)->header('Content-Length');

        return is_numeric($length) ? (int) $length : null;
    }

    protected function http(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('product-updater.source.url'), '/') . '/')
            ->timeout((int) config('product-updater.timeout', 300))
            ->acceptJson()
            ->retry(
                (int) config('product-updater.retries', 2),
                (int) config('product-updater.retry_delay', 250),
                static fn (Throwable $e): bool => $e instanceof ConnectionException
                    || ($e instanceof RequestException && (bool) $e->response->serverError()),
                throw: false,
            )
            ->withHeaders($this->headers());

        if (! (bool) config('product-updater.verify_tls', true)) {
            return $request->withoutVerifying();
        }

        return $request;
    }

    /**
     * Request headers for the update server. Overridden by source subclasses
     * (e.g. the Envato license-bridge uses LB-* headers).
     *
     * @return array<string, string>
     */
    protected function headers(): array
    {
        return array_filter(['X-API-KEY' => (string) config('product-updater.source.api_key')]);
    }

    /**
     * @param array<string, mixed>|null $data
     */
    protected function parse(?array $data): ?ProductRelease
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
            checksum: isset($data['checksum']) ? (string) $data['checksum'] : null,
            signature: isset($data['signature']) ? (string) $data['signature'] : null,
            minPhp: isset($data['min_php']) ? (string) $data['min_php'] : null,
        );
    }
}
