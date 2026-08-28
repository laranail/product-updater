<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Sources;

use Override;
use Throwable;

/**
 * Envato / Simtabi "license bridge" update source (Botble-style).
 *
 * Same release shape as {@see HttpUpdateSource} but authenticates with the LB-*
 * header set (API key, the installation URL, the resolved client IP and language)
 * and serves downloads from the `download_update/main/{id}` path. Selected with
 * `product-updater.source.driver = envato`.
 */
final class EnvatoUpdateSource extends HttpUpdateSource
{
    #[Override]
    public function checkConnection(): bool
    {
        try {
            return $this->http()->post('check_connection_ext')->successful();
        } catch (Throwable) {
            return false;
        }
    }

    #[Override]
    public function download(string $updateId, string $destination, ?string $licenseToken = null): void
    {
        $this->http()
            ->withOptions(['sink' => $destination])
            ->post('download_update/main/' . $updateId, array_filter([
                'product_id'    => config('product-updater.product_id'),
                'license_token' => $licenseToken,
            ]));
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function headers(): array
    {
        return array_filter([
            'LB-API-KEY' => (string) config('product-updater.source.api_key'),
            'LB-URL'     => rtrim((string) url('/'), '/'),
            'LB-IP'      => $this->clientIp(),
            'LB-LANG'    => 'english',
        ]);
    }

    private function clientIp(): string
    {
        $configured = (string) config('product-updater.source.ip', '');

        if ($configured !== '') {
            return $configured;
        }

        return request()->ip() ?? '127.0.0.1';
    }
}
