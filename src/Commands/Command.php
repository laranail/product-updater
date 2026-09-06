<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Commands;

use Simtabi\Laranail\Product\Updater\UpdateManager;
use Simtabi\Laranail\Console\Tools\Commands\Command as ConsoleCommand;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;

abstract class Command extends ConsoleCommand
{
    use SupportsNamespacedNames;

    protected function updater(): UpdateManager
    {
        return $this->laravel->make(UpdateManager::class);
    }

    protected function wantsJson(): bool
    {
        return $this->input->hasOption('json') && (bool) $this->input->getOption('json');
    }
}
