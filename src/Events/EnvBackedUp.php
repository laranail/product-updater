<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched after the host .env is backed up before applying an update.
 */
final readonly class EnvBackedUp
{
    use Dispatchable;

    public function __construct(public string $path) {}
}
