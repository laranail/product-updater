<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Commands;

use Simtabi\Laranail\Product\Updater\Exceptions\UpdaterException;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

final class UpdateCommand extends Command
{
    protected $signature = 'laranail::product-updater.update {--download-only : Download without extracting}';

    protected $description = 'Download and apply the latest product update (license-gated)';

    /** @var list<string> */
    protected array $commandAliases = ['product:update'];

    public function handle(): int
    {
        $release = $this->updater()->checkUpdate();

        if (! $release instanceof ProductRelease) {
            $this->services->display()->success(__('product-updater::product-updater.update.up_to_date'));

            return self::SUCCESS;
        }

        try {
            $archive = $this->services->interaction()->showSpinner(
                __('product-updater::product-updater.update.downloading', ['version' => $release->version]),
                fn (): string => $this->updater()->download($release),
            );

            if ($this->option('download-only')) {
                $this->services->display()->success(__('product-updater::product-updater.update.downloaded', ['path' => $archive]));

                return self::SUCCESS;
            }

            $this->services->interaction()->showSpinner(
                __('product-updater::product-updater.update.applying'),
                fn (): bool => $this->updater()->extract($archive),
            );

            $this->services->display()->success(__('product-updater::product-updater.update.updated', ['version' => $release->version]));

            return self::SUCCESS;
        } catch (UpdaterException $e) {
            $this->services->display()->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
