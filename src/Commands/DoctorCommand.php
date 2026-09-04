<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Commands;

use Simtabi\Laranail\Product\Updater\Doctor\Checks;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorReporter;

final class DoctorCommand extends Command
{
    protected $signature = 'laranail::product-updater.doctor {--json}';

    protected $description = 'Diagnose the product updater environment and configuration';

    /** @var list<string> */
    protected array $commandAliases = ['product:update-doctor'];

    public function handle(): int
    {
        return DoctorReporter::render($this, Checks::all(), (bool) $this->option('json'));
    }
}
