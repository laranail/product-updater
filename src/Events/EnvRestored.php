<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched after the host .env is restored from backup following a failed update.
 */
final readonly class EnvRestored
{
    use Dispatchable;

    public function __construct(public string $path) {}
}
