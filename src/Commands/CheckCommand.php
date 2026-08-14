<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Commands;

use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

final class CheckCommand extends Command
{
    protected $signature = 'laranail::product-updater.check {--json}';

    protected $description = 'Check whether a product update is available';

    /** @var list<string> */
    protected array $commandAliases = ['product:update-check'];

    public function handle(): int
    {
        $release = $this->updater()->checkUpdate();

        if ($this->wantsJson()) {
            $this->line((string) json_encode(['update' => $release?->toArray()], JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if (! $release instanceof ProductRelease) {
            $this->services->display()->success(__('laranail-product-updater::product-updater.check.up_to_date', ['version' => $this->updater()->currentVersion()]));

            return self::SUCCESS;
        }

        $this->services->display()->info(__('laranail-product-updater::product-updater.check.available', ['version' => $release->version]));
        $this->services->display()->keyValue(array_filter([
            __('laranail-product-updater::product-updater.check.version') => $release->version,
            __('laranail-product-updater::product-updater.check.released') => $release->releasedAt?->toDayDateTimeString(),
            __('laranail-product-updater::product-updater.check.summary') => $release->summary,
        ]));

        return self::SUCCESS;
    }
}
