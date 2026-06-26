<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Commands;

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

        if ($release === null) {
            $this->services->display()->success('You are running the latest version ('.$this->updater()->currentVersion().').');

            return self::SUCCESS;
        }

        $this->services->display()->info("Update available: v{$release->version}");
        $this->services->display()->keyValue(array_filter([
            'Version' => $release->version,
            'Released' => $release->releasedAt?->toDayDateTimeString(),
            'Summary' => $release->summary,
        ]));

        return self::SUCCESS;
    }
}
