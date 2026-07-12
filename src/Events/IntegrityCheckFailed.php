<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched when a downloaded archive fails its SHA-256 / signature check
 * (the file is deleted and the update aborts before extraction).
 */
final readonly class IntegrityCheckFailed
{
    use Dispatchable;

    public function __construct(
        public string $path,
        public string $reason,
    ) {}
}
