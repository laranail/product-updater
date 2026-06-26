<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Commands;

use Simtabi\Laranail\Console\Tools\Commands\Command as ConsoleCommand;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;
use Simtabi\Laranail\Product\Updater\UpdateManager;

abstract class Command extends ConsoleCommand
{
    use SupportsNamespacedNames;

    protected function updater(): UpdateManager
    {
        return $this->laravel->make(UpdateManager::class);
    }

    protected function wantsJson(): bool
    {
        return $this->hasOption('json') && (bool) $this->option('json');
    }
}
